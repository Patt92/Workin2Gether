<?php

namespace OCA\w2g2\Activity;

use OCP\Activity\IManager;
use OCP\App\IAppManager;
use OCP\Files\Config\IMountProviderCollection;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Share;
use OCP\Share\IShareHelper;

class Listener {
    /** @var IManager */
    protected $activityManager;
    /** @var IUserSession */
    protected $session;
    /** @var \OCP\App\IAppManager */
    protected $appManager;
    /** @var \OCP\Files\Config\IMountProviderCollection */
    protected $mountCollection;
    /** @var \OCP\Files\IRootFolder */
    protected $rootFolder;
    /** @var IShareHelper */
    protected $shareHelper;

    /**
     * Listener constructor.
     *
     * @param IManager $activityManager
     * @param IUserSession $session
     * @param IAppManager $appManager
     * @param IMountProviderCollection $mountCollection
     * @param IRootFolder $rootFolder
     * @param IShareHelper $shareHelper
     */
    public function __construct(IManager $activityManager,
                                IUserSession $session,
                                IAppManager $appManager,
                                IMountProviderCollection $mountCollection,
                                IRootFolder $rootFolder,
                                IShareHelper $shareHelper) {
        $this->activityManager = $activityManager;
        $this->session = $session;
        $this->appManager = $appManager;
        $this->mountCollection = $mountCollection;
        $this->rootFolder = $rootFolder;
        $this->shareHelper = $shareHelper;
    }

    public function fileLockEvent(FileLockEvent $event) {
        if ( ! $this->appManager->isInstalled('w2g2')) {
            return;
        }

        // Get all mount point owners
        $cache = $this->mountCollection->getMountCache();
        $mounts = $cache->getMountsForFileId((int)$event->getFileId());

        if (empty($mounts)) {
            return;
        }

        $users = [];
        foreach ($mounts as $mount) {
            $owner = $mount->getUser()->getUID();
            $ownerFolder = $this->rootFolder->getUserFolder($owner);
            $nodes = $ownerFolder->getById((int)$event->getFileId());

            if ( ! empty($nodes)) {
                /** @var Node $node */
                $node = array_shift($nodes);
                $al = $this->shareHelper->getPathsForAccessList($node);
                $users = array_merge($users, $al['users']);
            }
        }

        $actor = $this->session->getUser();
        if ($actor instanceof IUser) {
            $actor = $actor->getUID();
        } else {
            $actor = '';
        }

        $activity = $this->activityManager->generateEvent();
        $activity->setApp('w2g2')
            ->setType('w2g2')
            ->setAuthor($actor)
            ->setObject('files', (int) $event->getFileId())
            ->setMessage('file_lock_message', [
                'fileId' => $event->getFileId(),
            ]);

        foreach ($users as $user => $path) {
            $activity->setAffectedUser($user);

            $activity->setSubject($event->getEvent(), [
                'actor' => $actor,
                'fileId' => (int) $event->getFileId(),
                'filePath' => trim($path, '/'),
            ]);

            $this->activityManager->publish($activity);
        }
    }
}
