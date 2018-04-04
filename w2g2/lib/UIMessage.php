<?php

namespace OCA\w2g2;

use OCA\w2g2\Service\UserService;
use OCA\w2g2\Db\ConfigMapper;

class UIMessage
{
    protected $userNameRule;
    protected $l;

    public function __construct()
    {
        $this->userNameRule = ConfigMapper::getLockingByNameRule();
        
        $this->l = \OCP\Util::getL10N('w2g2');
    }

    public function getLocked($locker)
    {
        $locker = $this->userNameRule === "rule_username" ? $locker : UserService::getDisplayName($locker);

        return " " . $this->l->t("Locked by") . " " . $locker;
    }

    public function getNoPermission()
    {
        return " " . $this->l->t("No permission");
    }

    public function getUnlocked()
    {
        return " " . $this->l->t("Unlocked");
    }

    public function getDirectoryLockingNone()
    {
        return " " . $this->l->t("Directories locking is disabled");
    }

    public function getGroupFolderLockingNone()
    {
        return " " . $this->l->t("Group folder cannot be locked");
    }
}
