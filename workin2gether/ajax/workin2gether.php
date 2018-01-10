<?php

namespace OCA\workin2gether;

ini_set('display_errors', 1);

\OCP\Util::addTranslations('workin2gether');
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('workin2gether');
\OCP\JSON::callCheck();

$locker = new Locker();

echo $locker->handle($_POST);
