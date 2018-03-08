<?php

namespace OCA\w2g2;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

$mode = "";
$value = "";

try{
    $mode = $_POST['mode'];
    $value = $_POST['value'];
}
catch(Exception $e)
{	echo "Ajax modification detected";return; }

$l = \OCP\Util::getL10N( app::name );
$configvalue = \OC::$server->getConfig()->getAppValue( 'w2g2' , $mode , '[unset]' );

if( $configvalue == '[unset]' )//Value already set?
{
    $query = \OCP\DB::prepare("INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) VALUES('w2g2',?,?)");
    $result = $query->execute(array($mode,$value));
    echo $l->t($mode)." ".$l->t("has been set!");
}
else
{
    $query = \OCP\DB::prepare("UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='w2g2' and configkey=?");
    $result = $query->execute(array($value,$mode));
    echo $l->t("Updated successfully!");
}
