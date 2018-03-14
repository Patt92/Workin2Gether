<?php

namespace OCA\w2g2;

use OCA\w2g2\Migration\UpdateDatabase;

\OCP\Util::addTranslations('w2g2');
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('w2g2');
\OCP\JSON::callCheck();

$updateDatabase = new UpdateDatabase();

echo $updateDatabase->run();
