<?php

namespace OCA\w2g2;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Locker {
    protected $safe = null;
    protected $naming = "";
    protected $directoryLock = "";
    protected $l;

    protected $request = null;
    protected $database = null;

    public function __construct()
    {
        Database::fetch($this->naming, 'suffix', "rule_username");
        Database::fetch($this->directoryLock, 'directory_locking', "directory_locking_all");

        $this->l = \OCP\Util::getL10N('w2g2');
    }

    public function handle()
    {
        if (isset($_POST['safe'])) {
            $this->safe = $_POST['safe'];
        }

        if ( ! isset($_POST['batch'])) {
            return $this->handleSingleFile();
        }

        return $this->handleMultipleFiles();
    }

    protected function handleSingleFile()
    {
        $fileData = [];

        $fileData['path'] = stripslashes($_POST['path']);
        $fileData['path'] = Helpers::decodeCharacters($fileData['path']);

        if (isset($_POST['owner'])) {
            $fileData['owner'] = $_POST['owner'];
        }

        if (isset($_POST['id'])) {
            $fileData['id'] = $_POST['id'];
        }

        if (isset($_POST['mountType'])) {
            $fileData['mountType'] = $_POST['mountType'];
        }

        if (isset($_POST['fileType'])) {
            $fileData['fileType'] = $_POST['fileType'];
        }

        return $this->check($fileData);
    }

    protected function handleMultipleFiles()
    {
        $files = json_decode($_POST['path'], true);
        $folder = $_POST['folder'];

        for ($i = 0; $i < count($files); $i++) {
            $fileData = [];

            $fileName = $files[$i][1];

            $fileData['id'] = $files[$i][0];
            $fileData['owner'] = $files[$i][2];
            $fileData['path'] = $folder . $fileName;
            $fileData['mountType'] = $files[$i][4];
            $fileData['fileType'] = count($files[$i]) >= 5 ? $files[$i][5] : null;

            $response = $this->check($fileData);

            if ($response !== null) {
                $files[$i][3] = $response;
            }
        }

        return json_encode($files);
    }

    protected function check($fileData)
    {
        if ($this->fileFromGroupFolder($fileData['mountType']) || $this->fileFromSharing($fileData['owner'])) {
            $lockfile = $this->getLockpathFromExternalSource($fileData['id']);
        } else {
            $lockfile = $this->getLockpathFromCurrentUserFiles($fileData['path']);
        }

        $db_lock_state = $this->getLockStateForFile($lockfile, $fileData['fileType']);

        if ($db_lock_state != null) {
            $lockerUsername = $this->getUserThatLockedTheFile($db_lock_state);

            if ($this->safe === "false") {
                if ($this->currentUserIsTheOriginalLocker($lockerUsername)) {
                    $this->unlock($lockfile, $lockerUsername, $fileData['id']);

                    return " Unlocked.";
                }

                return " " . $this->l->t("No permission");
            } else {
                return $this->showLockedByMessage($lockerUsername, $this->l, false);
            }
        } else {
            if ($this->safe === "false") {
                if ($this->fileIsGroupFolder($fileData, $lockfile)) {
                    return " Group folder cannot be locked.";
                }

                $lockerUsername = $this->getCurrentUserName();

                $this->lock($lockfile,  $lockerUsername, $fileData['id']);

                return $this->showLockedByMessage($lockerUsername, $this->l, true);
            }
        }

        return null;
    }

    protected function fileFromGroupFolder($mountType)
    {
        return $mountType === 'group';
    }

    protected function fileFromSharing($owner)
    {
        return ! is_null($owner) && $owner !== '';
    }

    protected function getLockpathFromExternalSource($id)
    {
        $query = \OCP\DB::prepare("
          SELECT X.path, Y.id 
          FROM *PREFIX*filecache X 
          INNER JOIN *PREFIX*storages Y 
          ON X.storage = Y.numeric_id 
          WHERE X.fileid = ? LIMIT 1
    ");

        $result = $query->execute(array($id))
            ->fetchAll();

        $original_path = $result[0]['path'];
        $storage_id = str_replace("home::", "", $result[0]['id']) . '/';

        return $storage_id . $original_path;
    }

    protected function getLockpathFromCurrentUserFiles($path)
    {
        return \OCP\USER::getUser() . "/files" . Path::getClean($path);
    }

    /**
     * Must return an array with the first item having the key 'locked_by'
     * ex: db_lock_state[0]["locked_by"]
     *
     * @param $file
     * @param $fileType
     * @return mixed|null
     */
    protected function getLockStateForFile($file, $fileType)
    {
        $hasLock = Database::getFileLock($file);

        if ($hasLock != null) {
            return $hasLock;
        }

        if ($this->directoryLock === 'directory_locking_all') {
            return $this->checkFromAll($file);
        }

        return $this->checkFromParent($file, $fileType);
    }

    protected function getCurrentUserName()
    {
        return \OCP\User::getUser();
    }

    protected function getCurrentUserDisplayName()
    {
        return \OCP\User::getDisplayName();
    }

    protected function getUserThatLockedTheFile($db_lock_state)
    {
        return $db_lock_state[0]['locked_by'];
    }

    protected function showLockedByMessage($lockerUsername, $l, $isCurrentUser)
    {
        if ($this->naming === "rule_displayname") {
            $lockerUsername = $isCurrentUser
                ? $this->getCurrentUserDisplayName()
                : $this->getOtherUserDisplayName($lockerUsername);
        }

        return " " . $l->t("Locked") . " " . $l->t("by") . " " . $lockerUsername;
    }

    /**
     * Get a user's display name given his/hers username or the username if the display name is not set.
     *
     * @param $username
     * @return mixed
     */
    protected function getOtherUserDisplayName($username)
    {
        $usersMatchingUsername = Database::getUserByUsername($username);

        // check if the user is stored within the 'users' table and not in the 'accounts' table (ex: ldap)
        if (count($usersMatchingUsername) > 0 && array_key_exists("displayname", $usersMatchingUsername[0])) {
            return $usersMatchingUsername[0]["displayname"];
        }

        $ldapUsersMatchingUsername = Database::getUserByLDAPUsername($username);

        if (count($ldapUsersMatchingUsername) > 0) {
            $ldapUser = $ldapUsersMatchingUsername[0];
            $ldapUserData = $ldapUser['data'];

            $parsedJsonResults = json_decode($ldapUserData, true);

            // parse all fields from the data, find the attribute 'displayname'
            // and take the data from the value subattribute
            foreach ($parsedJsonResults as $attribute => $data) {
                if ($attribute === "displayname") {
                    return $data["value"];
                }
            }
        }

        return $username;
    }

    protected function currentUserIsTheOriginalLocker($owner)
    {
        return $owner === $this->getCurrentUserName();
    }

    protected function getFilePath($id)
    {
        $query = "SELECT path FROM *PREFIX*" . "filecache" . " WHERE fileid = ?";

        $filePath = \OCP\DB::prepare($query)
            ->execute(array($id))
            ->fetchAll();

        return $filePath;
    }

    /**
     * Check if locked from all parents above.
     *
     * @param $file
     * @return mixed|null
     */
    protected function checkFromAll($file) {
        $currentPath = Path::removeLastDirectory($file);

        while ($currentPath) {
            $hasLock = Database::getFileLock($currentPath);

            if ($hasLock != null) {
                return $hasLock;
            }

            $currentPath = Path::removeLastDirectory($currentPath);
        }

        return null;
    }

    /**
     * Check if the current file is locked from direct parent directory only.
     * Only if it is a file (directories not allowed).
     *
     * @param $file
     * @param $fileType
     * @return mixed
     */
    protected function checkFromParent($file, $fileType) {
        if ($fileType !== 'file') {
            return null;
        }

        $currentPath = Path::removeLastDirectory($file);

        return Database::getFileLock($currentPath);
    }

    protected function fileIsGroupFolder($fileData, $lockFile)
    {
        // if lockfile is group folder than it's value is something like: local::/var/www/...//__groupfolders/id
        $pathSteps = explode("/", $lockFile);
        $length = count($pathSteps);

        $notOrdinaryFolder = is_numeric($pathSteps[$length - 1]) && $pathSteps[$length - 2] === '__groupfolders';

        $isGroupFolder = $fileData['mountType'] === 'group' && $notOrdinaryFolder;

        return $fileData['fileType'] === 'dir' && $isGroupFolder;
    }

    protected function lock($lockfile, $lockedby_name, $fileId)
    {
        Database::lockFile($lockfile, $lockedby_name);

        $lockerUserDisplayName = $this->getOtherUserDisplayName($lockedby_name);

        Event::emit('lock', $fileId, $lockerUserDisplayName);
    }

    protected function unlock($lockfile, $lockedby_name, $fileId)
    {
        Database::unlockFile($lockfile);

        $lockerUserDisplayName = $this->getOtherUserDisplayName($lockedby_name);

        Event::emit('unlock', $fileId, $lockerUserDisplayName);
    }
}
