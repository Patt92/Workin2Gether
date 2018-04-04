<?php

namespace OCA\w2g2\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\w2g2\Db\Lock;
use OCA\w2g2\Db\LockMapper;
use OCA\w2g2\Db\ConfigMapper;
use OCA\w2g2\UIMessage;
use OCA\w2g2\File;

class LockService {
    protected $mapper;
    protected $uiMessage;
    protected $currentUser;

    public function __construct(LockMapper $mapper)
    {
        $this->mapper = $mapper;

        $this->currentUser = UserService::get();

        $this->uiMessage = new UIMessage();
    }

    public function handle($fileId, $fileType)
    {
        // Admin option to regarding directory locking is set to none.
        if (ConfigMapper::getDirectoryLock() === 'directory_locking_none' && $fileType === 'dir') {
            return $this->uiMessage->getDirectoryLockingNone();
        }

        $file = new File($fileId, $this->mapper);

        if ($file->isLocked()) {
            return $this->unlock($file);
        }

        return $this->lock($file);
    }

    public function lock($file)
    {
        if ($file->isGroupFolder()) {
            return $this->uiMessage->getGroupFolderLockingNone();
        }

        $this->create($file->getId());
        
        $file->onLocked();

        return $this->uiMessage->getLocked($this->currentUser);
    }

    public function unlock($file)
    {
        if ($file->canBeUnlockedBy($this->currentUser)) {
            $this->delete($file->getId());

            $file->onUnlocked();

            return $this->uiMessage->getUnlocked();
        }

        return $this->uiMessage->getNoPermission();
    }

    public function all() {
        return $this->mapper->findAll();
    }

    public function find($fileId) {
        try {
            return $this->mapper->find($fileId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($fileId) {
        $lock = new Lock();
        
        $lock->setFileId($fileId);
        $lock->setLockedBy($this->currentUser);

        return $this->mapper->store($lock);
    }

    public function delete($fileId) {
        try {
            $lock = $this->mapper->find($fileId);

            $this->mapper->deleteOne($lock);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function deleteAll()
    {
        return $this->mapper->deleteAll();
    }

    public function check($fileId, $fileType)
    {
        $file = new File($fileId, $this->mapper);

        if ($file->isLocked()) {
            return $this->uiMessage->getLocked($file->getLocker());
        }

        $directoryLock = ConfigMapper::getDirectoryLock();

        // Admin config to not check the upper directories.
        if ($directoryLock === 'directory_locking_none') {
            return '';
        }

        $fileParentId = $file->getParentId();
        $fileParent = new File($fileParentId, $this->mapper);
        $fileParentData = $fileParent->getCompleteData();

        // Root directory or a group folder root, so no parent.
        if ($fileParentData['path'] === 'files' || $fileParentData['path'] === '__groupfolders') {
            return '';
        }

        // Check the parent directory above, depending on the admin config.
        if ($directoryLock === 'directory_locking_files') {
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
            $upperDirectory = new File($upperDirectoryId, $this->mapper);

            if ($upperDirectory->isLocked()) {
                return $this->uiMessage->getLocked($upperDirectory->getLocker());
            }

            $currentDirectory = $upperDirectory;
            $currentDirectoryData = $upperDirectory->getCompleteData();
        }

        return '';
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}