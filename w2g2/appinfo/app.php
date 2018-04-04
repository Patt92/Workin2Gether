<?php

namespace OCA\w2g2;

use OCA\w2g2\Notification\Notifier;


class App {
    const name = 'w2g2';
    const table = 'locks_w2g2';

    public static function launch()
    {
        if (\OC_User::getUser() == false) {
            return;
        }

        \OCP\Util::addScript(self::name, 'w2g2');

        \OCP\Util::addStyle(self::name, 'styles');
    }
}

if (\OCP\App::isEnabled(App::name)) {
    App::launch();

    $notificationManager = \OC::$server->getNotificationManager();
    $notificationManager->registerNotifier(
        function() {
            $Application = new \OCP\AppFramework\App('w2g2');

            return $Application->getContainer()->query(Notifier::class);
        },
        function () {
            $l = \OC::$server->getL10N('w2g2');

            return ['id' => 'w2g2', 'name' => $l->t('w2g2')];
        }
    );
}
