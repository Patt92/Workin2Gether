<?php

namespace OCA\w2g2;

use OCA\w2g2\Activity\FileLockEvent;
use OCA\w2g2\Activity\EventHandler;

class Event {
    public static function emit($type, $fileId, $lockerUser)
    {
        $eventType = $type === 'lock' ? FileLockEvent::EVENT_LOCK : FileLockEvent::EVENT_UNLOCK;

        $fileLockEvent = new FileLockEvent($eventType, $fileId, $lockerUser);

        $app = new \OCP\AppFramework\App('w2g2');

        $eventHandler = $app->getContainer()->query(EventHandler::class);
        $eventHandler->handle($fileLockEvent);
    }
}
