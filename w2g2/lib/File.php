<?php

namespace OCA\w2g2;

use OCA\w2g2\Service\UserService;
use OCA\w2g2\Db\GroupFolderMapper;
use OCA\w2g2\Db\FileMapper;

class File {
    protected $fileId;

    // @var OCA\w2g2\Db\Lock
    protected $lock;

    public function __construct($fileId, $mapper)
    {
        $this->fileId = $fileId;

        try {
            $this->lock = $mapper->find($this->fileId);
        } catch (\Exception $e) {
            $this->lock = null;
        }
    }

    public function isLocked()
    {
        return !! $this->lock;
    }

    public function getId()
    {
        return $this->fileId;
    }

    public function getLocker()
    {
        if ( ! $this->lock) {
            return null;
        }

        return $this->lock->getLockedBy();
    }

    public function onLocked()
    {
        Event::emit('lock', $this->fileId, UserService::getDisplayName());
    }

    public function onUnlocked()
    {
        Event::emit('unlock', $this->fileId, UserService::getDisplayName($this->getLocker()));
    }

    public function canBeUnlockedBy($user)
    {
        return $this->getLocker() === $user;
    }

    public function isGroupFolder()
    {
        $groupFolderFileId = GroupFolderMapper::get();

        if ( ! $groupFolderFileId) {
            return false;
        }

        return $this->getParentId() === $groupFolderFileId;
    }

    public function getParentId()
    {
        return ($this->getCompleteData())['parent'];
    }

    public function getCompleteData()
    {
        return FileMapper::get($this->fileId);
    }
}
