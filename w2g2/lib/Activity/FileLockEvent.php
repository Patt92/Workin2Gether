<?php

namespace OCA\w2g2\Activity;

use Symfony\Component\EventDispatcher\Event;

class FileLockEvent extends Event {
    const EVENT_LOCK   = 'file_lock_subject';
    const EVENT_UNLOCK = 'file_unlock_subject';

    protected $event;
    protected $fileId;
    protected $lockerUser;

    public function __construct($eventType, $fileId, $lockerUser)
    {
        $this->event = $eventType;
        $this->fileId = $fileId;
        $this->lockerUser = $lockerUser;
    }
    
    public function getEvent()
    {
        return $this->event;
    }

    public function getFileId()
    {
        return $this->fileId;
    }

    public function getLockerUser()
    {
        return $this->lockerUser;
    }

    public function getLockEventName()
    {
        return self::EVENT_LOCK;
    }

    public function getUnlockEventName()
    {
        return self::EVENT_UNLOCK;
    }
}
