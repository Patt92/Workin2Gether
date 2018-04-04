<?php

namespace OCA\w2g2\Activity;

use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\Activity\IProvider;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCA\w2g2\Service\UserService;

class Provider implements IProvider {

    /** @var IFactory */
    protected $languageFactory;

    /** @var IL10N */
    protected $l;

    /** @var IURLGenerator */
    protected $url;

    /** @var IUserManager */
    protected $userManager;

    /** @var IManager */
    protected $activityManager;

    /** @var string[] */
    protected $displayNames = [];

    /**
     * @param IFactory $languageFactory
     * @param IURLGenerator $url
     * @param IUserManager $userManager
     * @param IManager $activityManager
     */
    public function __construct(
        IFactory $languageFactory,
        IURLGenerator $url,
        IUserManager $userManager,
        IManager $activityManager
    ) {
        $this->languageFactory = $languageFactory;
        $this->url = $url;
        $this->userManager = $userManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @param string $language
     * @param IEvent $event
     * @param IEvent|null $previousEvent
     * @return IEvent
     * @throws \InvalidArgumentException
     * @since 11.0.0
     */
    public function parse($language, IEvent $event, IEvent $previousEvent = null)
    {
        if ($event->getApp() !== 'w2g2') {
            throw new \InvalidArgumentException();
        }

        $this->l = $this->languageFactory->get('w2g2', $language);

        $allow = $event->getSubject() === 'file_lock_subject' || $event->getSubject() === 'file_unlock_subject';

        if ($allow) {
            $iconName = $event->getSubject() === 'file_lock_subject' ? 'lock' : 'unlock';
            $iconName .= $this->activityManager->getRequirePNG() ? '.png' : '.svg';

            $event->setIcon($this->url->getAbsoluteURL($this->url->imagePath('w2g2', 'actions/' . $iconName)));

            if ($this->activityManager->isFormattingFilteredObject()) {
                try {
                    return $this->parseShortVersion($event);
                } catch (\InvalidArgumentException $e) {
                    // Ignore and simply use the long version...
                }
            }

            return $this->parseLongVersion($event);
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param IEvent $event
     * @return IEvent
     * @throws \InvalidArgumentException
     */
    protected function parseShortVersion(IEvent $event) {
        $subjectParameters = $this->getSubjectParameters($event);
        $currentUserIsLocker = $subjectParameters['actor'] === $this->activityManager->getCurrentUserId();
        $lockingAction = $event->getSubject() === 'file_lock_subject';
        $action = $lockingAction ? 'locked' : 'unlocked';

        if ($currentUserIsLocker) {
            $event->setParsedSubject($this->l->t('You ' . $action . ' the file'))
                ->setRichSubject($this->l->t('You ' . $action . ' the file'), []);
        } else {
            $author = $this->generateUserParameter($subjectParameters['actor']);
            $event->setParsedSubject($this->l->t('%1$s ' . $action . ' the file', [$author['name']]))
                ->setRichSubject($this->l->t('{author} ' . $action . ' the file'), [
                    'author' => $author,
                ]);
        }

        return $event;
    }

    /**
     * @param IEvent $event
     * @return IEvent
     * @throws \InvalidArgumentException
     */
    protected function parseLongVersion(IEvent $event) {
        $subjectParameters = $this->getSubjectParameters($event);
        $currentUserIsLocker = $subjectParameters['actor'] === $this->activityManager->getCurrentUserId();
        $lockingAction = $event->getSubject() === 'file_lock_subject';
        $action = $lockingAction ? 'locked' : 'unlocked';

        if ($currentUserIsLocker) {
            $event->setParsedSubject($this->l->t('You ' . $action . ' the file: %1$s', [
                $subjectParameters['filePath'],
            ]))
                ->setRichSubject($this->l->t('You ' . $action . ' the file: {file}'), [
                    'file' => $this->generateFileParameter($subjectParameters['fileId'], $subjectParameters['filePath']),
                ]);
        } else {
            $author = $this->generateUserParameter($subjectParameters['actor']);

            $event->setParsedSubject($this->l->t('%1$s ' . $action . ' the file: %2$s', [
                $author['name'],
                $subjectParameters['filePath'],
            ]))
                ->setRichSubject($this->l->t('{author} ' . $action . ' the file: {file}'), [
                    'author' => $author,
                    'file' => $this->generateFileParameter($subjectParameters['fileId'], $subjectParameters['filePath']),
                ]);
        }

        return $event;
    }

    protected function getSubjectParameters(IEvent $event) {
        $subjectParameters = $event->getSubjectParameters();
        if (isset($subjectParameters['fileId'])) {
            return $subjectParameters;
        }

        // Fix subjects from 12.0.3 and older
        //
        // Do NOT Remove unless necessary
        // Removing this will break parsing of activities that were created on
        // Nextcloud 12, so we should keep this as long as it's acceptable.
        // Otherwise if people upgrade over multiple releases in a short period,
        // they will get the dead entries in their stream.
        return [
            'actor' => $subjectParameters[0],
            'fileId' => (int) $event->getObjectId(),
            'filePath' => trim($subjectParameters[1], '/'),
        ];
    }

    /**
     * @param int $id
     * @param string $path
     * @return array
     */
    protected function generateFileParameter($id, $path) {
        return [
            'type' => 'file',
            'id' => $id,
            'name' => basename($path),
            'path' => $path,
            'link' => $this->url->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileid' => $id]),
        ];
    }

    /**
     * @param string $uid
     * @return array
     */
    protected function generateUserParameter($uid) {
        if (!isset($this->displayNames[$uid])) {
            $this->displayNames[$uid] = UserService::getDisplayName($uid);
        }

        return [
            'type' => 'user',
            'id' => $uid,
            'name' => $this->displayNames[$uid],
        ];
    }
}
