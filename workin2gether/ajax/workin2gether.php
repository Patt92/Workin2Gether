<?php

namespace OCA\workin2gether;

\OCP\Util::addTranslations('workin2gether');
$l = \OCP\Util::getL10N('workin2gether');

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('workin2gether');
\OCP\JSON::callCheck();

$safe = null;

$naming = "";
db_fetcher($naming, 'suffix', "rule_username");

$extended = "";
db_fetcher($extended, 'extended', "0");

if (isset($_POST['safe'])) {
	$safe = $_POST['safe'];
}

$owner = null;
$id = null;
$path = null;
if (!isset($_POST['batch'])){

	$path = stripslashes($_POST['path']);
	if (isset($_POST['owner'])) {
		$owner = $_POST['owner'];
	}

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	}

	echo check( $id , $owner , $path , $safe , $extended , $naming , $l);
}
else{
	$files = json_decode($_POST['path'],true);
	$folder = $_POST['folder'];
	for( $i = 0; $i < count($files); $i++ )
	{
		$ret = check( $files[$i][0] , $files[$i][2] , $folder.$files[$i][1] , $safe , $extended , $naming , $l );
		if ($ret !== null)
			$files[$i][3] = $ret;
	}
	echo json_encode($files);
}

function check( $_id , $_owner , $_path , $_safe , $_extended , $_naming , $l )
{
	if (!is_null($_owner) && $_owner !== '') {
		$query = \OCP\DB::prepare("SELECT X.path, Y.id FROM *PREFIX*filecache X INNER JOIN *PREFIX*storages Y ON X.storage = Y.numeric_id where X.fileid = ? LIMIT 1");
		$result = $query->execute(array($_id))->fetchAll();
		$original_path = $result[0]['path'];
		$storage_id = str_replace("home::", "", $result[0]['id']) . '/';
		$lockpath = $storage_id.$original_path;
	}
	else $lockpath = \OCP\USER::getUser()."/files".cleanPath($_path);

	$lockfile = $lockpath;
	$db_lock_state = \OCP\DB::prepare("SELECT * FROM *PREFIX*" . app::table . " WHERE name = ?")->execute(array($lockfile))->fetchAll();

	if ($db_lock_state!=null) {
		if( $_safe === "false" ) {
			$lockedby_name = $db_lock_state[0]['locked_by'];
                	ShowUserName($lockedby_name);

			if( extended_precheck( $_extended, $lockedby_name ) != 0 ){
				return " ".$l->t("No permission");
			}

			\OCP\DB::prepare("DELETE FROM *PREFIX*" . app::table . " WHERE name=?")->execute(array($lockfile));
		}
		else{
			$lockedby_name = $db_lock_state[0]['locked_by'];
			ShowDisplayName($lockedby_name);
			return " ".$l->t("Locked")." ".$l->t("by")." ".$lockedby_name;
		}
	}
	else {
		if( $_safe === "false" ) {
			$lockedby_name = \OCP\User::getUser();
			if($_naming=="rule_displayname") {
				$lockedby_name = \OCP\User::getDisplayName();
				$lockedby_name .= "|".\OCP\User::getUser();
			}

			\OCP\DB::prepare("INSERT INTO *PREFIX*".app::table."(name,locked_by) VALUES(?,?)")->execute(array($lockfile,$lockedby_name));

			ShowDisplayName($lockedby_name); //Korrektur bei DisplayName
			return " ".$l->t("Locked")." ".$l->t("by")." ".$lockedby_name;
		}
	}
	return null;
}

function cleanPath($path){
	return preg_replace('{(/)\1+}', "/", urldecode(rtrim($path, "/")));
}

function db_fetcher( &$configkey, $configtype , $_default){
	$type = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey=? and appid='workin2gether' LIMIT 1")->execute(array($configtype))->fetchAll();

	if (count($type) >= 1)
        	$configkey = $type[0]['configvalue'];
	else $configkey = $_default;
}

function extended_precheck( $extended, $owner ){
	if ( $extended == "0" )
		return 0;
	elseif ( $extended == "1" )
	{
		if( $owner == \OCP\User::getUser() )
			return 0;
	}

	return 1;
}

function ShowDisplayName(&$lockedby_name){
	if(strstr($lockedby_name,"|")){
                $temp_ln = explode("|",$lockedby_name);
                $lockedby_name = $temp_ln[0];
        }
}

function ShowUserName(&$lockedby_name){
        if(strstr($lockedby_name,"|")){
                $temp_ln = explode("|",$lockedby_name);
                $lockedby_name = $temp_ln[1];
        }
}

