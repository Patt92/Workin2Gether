<?php

namespace OCA\w2g2;

use OCA\w2g2\Activity\FileLockEvent;
use OCA\w2g2\Activity\EventHandler;

class Event {
    public static function emit($type, $fileId)
    {
        $eventType = $type === 'lock' ? FileLockEvent::EVENT_LOCK : FileLockEvent::EVENT_UNLOCK;

        $event = new FileLockEvent($eventType, $fileId);

        $app = new \OCP\AppFramework\App('w2g2');

        $eventHandler = $app->getContainer()->query(\OCA\w2g2\Activity\EventHandler::class);
        $eventHandler->handle($event);
    }
}
