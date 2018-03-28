<?php

namespace OCA\w2g2\Activity;

use OCA\w2g2\Activity\Listener as ActivityListener;
use OCA\w2g2\Notification\Listener as NotificationListener;

class EventHandler {
    private $activityListener;
    private $notificationListener;

    public function __construct(ActivityListener $activityListener, NotificationListener $notificationListener) {
        $this->activityListener = $activityListener;
        $this->notificationListener = $notificationListener;
    }

    public function handle(FileLockEvent $event) {
        $this->activityHandler($event);
        $this->notificationHandler($event);
    }

    private function activityHandler(FileLockEvent $event) {
        $this->activityListener->fileLockEvent($event);
    }

    /**
     * Notify all users that set the file as favorite, if any.
     *
     * @param \OCA\w2g2\Activity\FileLockEvent $event
     */
    private function notificationHandler(FileLockEvent $event) {
        $this->notificationListener->handle($event);
    }
}
