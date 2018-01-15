<?php

namespace OCA\w2g2\Activity;

use OCA\w2g2\Activity\Listener as ActivityListener;
use OCA\w2g2\Activity\FileLockEvent;
//use OCA\Comments\Notification\Listener as NotificationListener;

class EventHandler {
    private $activityListener;
    private $notificationListener;

    public function __construct(ActivityListener $activityListener) {
        $this->activityListener = $activityListener;

//        $this->notificationListener = $notificationListener;
    }

    public function handle(FileLockEvent $event) {
//        if ($event->getFile()->getObjectType() !== 'files') {
//            // this is a 'files'-specific Handler
//            return;
//        }

        $eventType = $event->getEvent();

        // if the file is set as favourite then send the user a notification
        // not important for now

//        if ($eventType === CommentsEvent::EVENT_ADD) {
//            $this->notificationHandler($event);
//            $this->activityHandler($event);
//            return;
//        }
//
//        $applicableEvents = [
//            CommentsEvent::EVENT_PRE_UPDATE,
//            CommentsEvent::EVENT_UPDATE,
//            CommentsEvent::EVENT_DELETE,
//        ];
//        if(in_array($eventType, $applicableEvents)) {
//            $this->notificationHandler($event);
//            return;
//        }

        $this->activityHandler($event);
    }

    private function activityHandler(FileLockEvent $event) {
        $this->activityListener->fileLockEvent($event);
    }

//    private function notificationHandler(CommentsEvent $event) {
//        $this->notificationListener->evaluate($event);
//    }
}
