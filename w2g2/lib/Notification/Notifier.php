<?php

namespace OCA\w2g2\Notification;

use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCA\w2g2\Database;
use OCA\w2g2\User;

use \OCP\ILogger;

class Notifier implements INotifier {

    /** @var IFactory */
    protected $l10nFactory;

    /** @var IRootFolder  */
    protected $rootFolder;

    /** @var IURLGenerator */
    protected $url;

    /** @var IUserManager */
    protected $userManager;

    protected $logger;

    public function __construct(
        IFactory $l10nFactory,
        IRootFolder $rootFolder,
        IURLGenerator $url,
        IUserManager $userManager,

        ILogger $logger
    ) {
        $this->l10nFactory = $l10nFactory;
        $this->rootFolder = $rootFolder;
        $this->url = $url;
        $this->userManager = $userManager;

        $this->logger = $logger;
    }

    /**
     * @param INotification $notification
     * @param string $languageCode The code of the language that should be used to prepare the notification
     * @return INotification
     * @throws \InvalidArgumentException When the notification was not prepared by a notifier
     */
    public function prepare(INotification $notification, $languageCode) {
        if ($notification->getApp() !== 'w2g2') {
            throw new \InvalidArgumentException();
        }

        $l = $this->l10nFactory->get('w2g2', $languageCode);

        $parameters = $notification->getSubjectParameters();

        if ($parameters[0] !== 'files') {
            throw new \InvalidArgumentException('Unsupported file lock object');
        }

        $notifiedUser = $notification->getUser();
        $fileId = (int)$parameters[1];

        $userFolder = $this->rootFolder->getUserFolder($notifiedUser);
        $nodes = $userFolder->getById($fileId);

        if (empty($nodes)) {
            throw new \InvalidArgumentException('Cannot resolve file ID to node instance');
        }

        $file = $nodes[0];
        $lockerUser = $parameters[2];
        $lockerUserDisplayName = User::getDisplayName($lockerUser);
        $eventType = $parameters[3];

        $notification->setParsedSubject(
            $l->t(
                '%1$s ' . $eventType . 'ed the file you have added to your favorites: “%2$s”',
                [$lockerUserDisplayName, $file->getName()]
            )
        )->setRichSubject(
            $l->t('{user} ' . $eventType . 'ed the file you have added to your favorites: {file}'),
            [
                'user' => [
                    'type' => 'user',
                    'id' => $lockerUser,
                    'name' => $lockerUserDisplayName
                ],
                'file' => [
                    'type' => 'file',
                    'id' => $fileId,
                    'name' => $file->getName(),
                    'path' => $file->getPath(),
                    'link' => $this->url->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileid' => $fileId]),
                ],
            ]
        );

        $notification->setIcon(
            $this->url->getAbsoluteURL($this->url->imagePath('w2g2', 'actions/' . $eventType . '.png'))
        );

        return $notification;
    }
}
