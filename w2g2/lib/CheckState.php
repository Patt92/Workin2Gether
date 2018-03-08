<?php

namespace OCA\w2g2;

class CheckState
{
    protected $uiMessage;
    protected $directoryLock = "";
    protected $files;
    protected $folder;

    public function __construct($files, $folder)
    {
        $this->uiMessage = new UIMessage();

        Database::fetch($this->directoryLock, 'directory_locking', "directory_locking_all");

        $this->files = json_decode($files, true);
        $this->folder = $folder;
    }

    public function handle()
    {
        for ($i = 0; $i < count($this->files); $i++) {
            $fileData = [];

            $fileName = $this->files[$i][1];

            $fileData['id'] = $this->files[$i][0];
            $fileData['owner'] = $this->files[$i][2];
            $fileData['path'] = $this->folder . $fileName;
            $fileData['mountType'] = $this->files[$i][4];
            $fileData['fileType'] = count($this->files[$i]) >= 5 ? $this->files[$i][5] : null;

            $response = $this->check($fileData['id'], $fileData['fileType']);

            if ($response !== null) {
                $this->files[$i][3] = $response;
            }
        }

        return json_encode($this->files);
    }

    protected function check($fileId, $fileType)
    {
        $file = new File($fileId);

        if ($file->isLocked()) {
            return $this->uiMessage->getLocked($file->getLocker());
        }

        // Admin config to not check the upper directories.
        if ($this->directoryLock === 'directory_locking_none') {
            return '';
        }

        $fileParentId = $file->getParentId();
        $fileParent = new File($fileParentId);
        $fileParentData = $fileParent->getCompleteData();

        // Root directory or a group folder root, so no parent.
        if ($fileParentData['path'] === 'files' || $fileParentData['path'] === '__groupfolders') {
            return '';
        }

        // Check the parent directory above, depending on the admin config.
        if ($this->directoryLock === 'directory_locking_files') {
            if ($fileType === 'file' && $fileParent->isLocked()) {
                return $this->uiMessage->getLocked($fileParent->getLocker());
            }

            return '';
        }

        // Check all parent directories above, depending on the admin config.
        // $this->directoryLock === 'directory_locking_all'
        if ($fileParent->isLocked()) {
            return $this->uiMessage->getLocked($fileParent->getLocker());
        }

        $currentDirectory = $fileParent;
        $currentDirectoryData = $currentDirectory->getCompleteData();

        while ($currentDirectoryData['path'] !== 'files' && $currentDirectoryData['path'] !== '__groupfolders') {
            $upperDirectoryId = $currentDirectory->getParentId();
            $upperDirectory = new File($upperDirectoryId);

            if ($upperDirectory->isLocked()) {
                return $this->uiMessage->getLocked($upperDirectory->getLocker());
            }

            $currentDirectory = $upperDirectory;
            $currentDirectoryData = $upperDirectory->getCompleteData();
        }

        return '';
    }
}
