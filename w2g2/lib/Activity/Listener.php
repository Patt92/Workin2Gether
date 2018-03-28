<?php

namespace OCA\w2g2\Activity;

use OCP\Activity\IManager;
use OCP\App\IAppManager;
use OCP\Files\Node;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Share;
use OCP\Files\Config\ICachedMountFileInfo;
use OCP\Files\Config\ICachedMountInfo;
use OCP\Files\Config\IUserMountCache;

class Listener {
    /** @var IManager */
    protected $activityManager;

    /** @var IUserSession */
    protected $session;

    /** @var \OCP\App\IAppManager */
    protected $appManager;

    /** @var IUserMountCache  */
    protected $userMountCache;

    /**
     * Listener constructor.
     *
     * @param IManager $activityManager
     * @param IUserSession $session
     * @param IAppManager $appManager
     * @param IUserMountCache $userMountCache
     */
    public function __construct(IManager $activityManager,
                                IUserSession $session,
                                IAppManager $appManager,
                                IUserMountCache $userMountCache) {
        $this->activityManager = $activityManager;
        $this->session = $session;
        $this->appManager = $appManager;
        $this->userMountCache = $userMountCache;
    }

    /**
     * Generate the event and dispatch it.
     *
     * @param FileLockEvent $event
     */
    public function fileLockEvent(FileLockEvent $event) {
        if ( ! $this->appManager->isInstalled('w2g2')) {
            return;
        }

        $mountsForFile = $this->getMountsForFile($event);

        if ( ! $mountsForFile || count($mountsForFile) === 0) {
            return;
        }

        $userIds = $this->getAffectedUserIds($mountsForFile);

        $paths = $this->getAffectedPaths($mountsForFile);

        $actor = $this->getActor();

        $activity = $this->activityManager->generateEvent();
        $activity->setApp('w2g2')
            ->setType('w2g2')
            ->setAuthor($actor)
            ->setObject('files', (int) $event->getFileId())
            ->setMessage('file_lock_message', [
                'fileId' => $event->getFileId(),
            ]);

        for ($i = 0; $i < count($userIds); $i++) {
            $activity->setAffectedUser($userIds[$i]);

            $activity->setSubject($event->getEvent(), [
                'actor' => $actor,
                'fileId' => (int) $event->getFileId(),
                'filePath' => trim($paths[$i], '/'),
            ]);

            $this->activityManager->publish($activity);
        }
    }

    /**
     * Get all mounts associated with the file acted upon.
     *
     * @param FileLockEvent $event
     * @return mixed
     */
    protected function getMountsForFile(FileLockEvent $event)
    {
        return $this->userMountCache->getMountsForFileId((int)$event->getFileId());
    }

    /**
     * Get all users that own the mounts associated with the file acted upon.
     *
     * @param array $mountsForFile
     * @return array
     */
    protected function getAffectedUserIds(array $mountsForFile)
    {
        $affectedUserIds = array_map(function (ICachedMountInfo $mount) {
            return $mount->getUser()->getUID();
        }, $mountsForFile);

        return array_values($affectedUserIds);
    }

    /**
     * Get all file paths for the mounts associated with the file acted upon.
     *
     * @param array $mountsForFile
     * @return array
     */
    protected function getAffectedPaths(array $mountsForFile)
    {
        $affectedPaths =  array_map(function (ICachedMountFileInfo $mount) {
            return $mount->getPath();
        }, $mountsForFile);

        return array_values($affectedPaths);
    }

    /**
     * Get the user that acted upon the file.
     *
     * @return string
     */
    protected function getActor()
    {
        $actor = $this->session->getUser();

        if ($actor instanceof IUser) {
            return $actor->getUID();
        }

        return '';
    }
}
