<?php

namespace OCA\w2g2\Activity;

use Symfony\Component\EventDispatcher\Event;

class FileLockEvent extends Event {
    const EVENT_LOCK   = 'file_lock_subject';
    const EVENT_UNLOCK = 'file_unlock_subject';

    protected $event;
    protected $fileId;

    public function __construct($event, $fileId) {
        $this->event = $event;
        $this->fileId = $fileId;
    }

    public function getEvent() {
        return $this->event;
    }

    public function getFileId() {
        return $this->fileId;
    }
}
