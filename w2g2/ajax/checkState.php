<?php

namespace OCA\w2g2;

\OCP\Util::addTranslations('w2g2');
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('w2g2');
\OCP\JSON::callCheck();

$checkState = new CheckState($_POST['files'], $_POST['folder']);

echo $checkState->handle();
