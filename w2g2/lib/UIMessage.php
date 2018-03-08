<?php

namespace OCA\w2g2;

class UIMessage
{
    protected $naming = "";
    protected $l;

    public function __construct()
    {
        Database::fetch($this->naming, 'suffix', "rule_username");

        $this->l = \OCP\Util::getL10N('w2g2');
    }

    public function getLocked($locker)
    {
        $locker = $this->naming === "rule_displayname" ? User::getDisplayName($locker) : $locker;

        return " " . $this->l->t("Locked by") . " " . $locker;
    }

    public function getNoPermission()
    {
        return " " . $this->l->t("No permission");
    }

    public function getUnlocked()
    {
        return " Unlocked.";
    }
}
