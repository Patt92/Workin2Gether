<?php

namespace OCA\w2g2;

\OCP\Util::addTranslations('w2g2');

$l = \OCP\Util::getL10N('w2g2');

class app {
    const name = 'w2g2';
    const table = 'locks_w2g2';

    public static function launch()
    {
        if (\OC_User::getUser() == false) {
            return;
        }

        \OCP\Util::addScript(self::name, 'w2g2');

        \OCP\Util::addstyle(self::name, 'styles');

        \OCP\App::registerAdmin(self::name, 'admin');
    }
}

if (\OCP\App::isEnabled(app::name)) {

    if ( ! \OC::$server->getDatabaseConnection()->tableExists(app::table)) {
        try {
            $statement = "CREATE table *PREFIX*" . app::table . "(file_id INTEGER PRIMARY KEY, locked_by varchar(255), created TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";

            $db_exist = \OCP\DB::prepare($statement);
            $db_exist->execute();
        } catch(Exception $e) {
        }
    }

    app::launch();

    $notificationManager = \OC::$server->getNotificationManager();
    $notificationManager->registerNotifier(
        function() {
            $application = new \OCP\AppFramework\App('w2g2');

            return $application->getContainer()->query(\OCA\w2g2\Notification\Notifier::class);
        },
        function () {
            $l = \OC::$server->getL10N('w2g2');

            return ['id' => 'w2g2', 'name' => $l->t('w2g2')];
        }
    );
}
