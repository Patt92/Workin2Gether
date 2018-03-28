<?php

namespace OCA\w2g2;

class Locker
{
    protected $uiMessage;
    protected $naming = "";
    protected $directoryLock = "";
    protected $fileData = [];

    public function __construct($request)
    {
        $this->uiMessage = new UIMessage();

        Database::fetch($this->naming, 'suffix', "rule_username");
        Database::fetch($this->directoryLock, 'directory_locking', "directory_locking_all");

        $this->fileData['path'] = stripslashes($request['path']);
        $this->fileData['path'] = Helpers::decodeCharacters($this->fileData['path']);

        if (isset($request['owner'])) {
            $this->fileData['owner'] = $request['owner'];
        }

        if (isset($request['id'])) {
            $this->fileData['id'] = $request['id'];
        }

        if (isset($request['mountType'])) {
            $this->fileData['mountType'] = $request['mountType'];
        }

        if (isset($request['fileType'])) {
            $this->fileData['fileType'] = $request['fileType'];
        }
    }

    public function handle()
    {
        $file = new File($this->fileData['id']);

        if ($file->isLocked()) {
            return $this->unlock($file);
        }

        return $this->lock($file);
    }

    protected function unlock($file)
    {
        if ($file->canBeUnlockedBy(User::getCurrentUserName())) {
            $file->unlock();

            return $this->uiMessage->getUnlocked();
        }

        return $this->uiMessage->getNoPermission();
    }

    protected function lock($file)
    {
        if ($file->isGroupFolder()) {
            return " Group folder cannot be locked.";
        }

        $file->lock(User::getCurrentUserName());

        return $this->uiMessage->getLocked(User::getCurrentUserName());
    }
}
