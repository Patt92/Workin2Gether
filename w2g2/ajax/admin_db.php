<?php

namespace OCA\w2g2;

\OCP\JSON::checkAdminUser();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

switch($_POST['action'])
{
    case 'clearall':
        \OC::$server->getDatabaseConnection()->prepare("TRUNCATE *PREFIX*".app::table)->execute();
        echo "clear";
        break;

    case 'clearthis':
        \OC::$server->getDatabaseConnection()
            ->prepare("DELETE FROM *PREFIX*".app::table." WHERE name=?")
            ->execute(array($_POST['lock']));

        echo "clear";

        break;

    default;
        break;
}
