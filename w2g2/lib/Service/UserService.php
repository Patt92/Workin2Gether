<?php

namespace OCA\w2g2\Service;

use OCP\IUser;

class UserService {
    public static function get()
    {
        return \OC::$server->getUserSession()->getUser()->getUID();
    }

    public static function getDisplayName($userName = null)
    {
        if ( ! $userName) {
            $userName = static::get();
        }

        $userManager = \OC::$server->getUserManager();

        $user = $userManager->get($userName);

        if ($user instanceof IUser) {
            return $user->getDisplayName();
        }

        return $userName;
    }
}
