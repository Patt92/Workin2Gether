<?php

namespace OCA\w2g2;

ini_set('display_errors', 1);

\OCP\Util::addTranslations('w2g2');
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('w2g2');
\OCP\JSON::callCheck();

$locker = new Locker();

echo $locker->handle($_POST);
