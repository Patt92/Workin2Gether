<?php

namespace OCA\workin2gether;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

$mode = "";
$value = "";

try {
    $mode = $_POST['mode'];
    $value = $_POST['value'];
} catch (Exception $e) {
    echo "Ajax modification detected";

    return;
}

$l = \OCP\Util::getL10N(app::name);
$configvalue = \OC::$server->getConfig()->getAppValue('workin2gether', $mode, '[unset]');

//Value already set?
if ($configvalue == '[unset]') {
    $query = \OCP\DB::prepare("INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) VALUES('workin2gether',?,?)");
    $result = $query->execute(array($mode, $value));

    echo $l->t($mode) . " " . $l->t("has been set!");
} else {
    $query = \OCP\DB::prepare("UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='workin2gether' and configkey=?");
    $result = $query->execute(array($value, $mode));

    echo $l->t("Updated successfully!");
}
