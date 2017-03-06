<?php

namespace OCA\workin2gether;

//Init translations
\OCP\Util::addTranslations('workin2gether');
$l = \OCP\Util::getL10N('workin2gether');

//Requirements check
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('workin2gether');
\OCP\JSON::callCheck();

////////////////////////////////////////////////////
////	variables for lock management			////
/**/	$path = stripslashes($_POST['path']) ; 	/**/
/**/	$safe = null;							/**/
/**/	$owner = null;							/**/
/**/	$id = null;								/**/
////////////////////////////////////////////////////

//Naming = Showing the name, who locked the File
//Implemented: rule_username|rule_displayname
$naming = "rule_username";
db_fetcher($naming, 'suffix' );

$extended = "0";
db_fetcher($extended, 'extended');

//Get all POST type variables
if (isset($_POST['safe'])) {
	$safe = $_POST['safe'];
}

if (isset($_POST['owner'])) {
	$owner = $_POST['owner'];
}

if (isset($_POST['id'])) {
	$id = $_POST['id'];
}
//<- End POST type variables section

$ppath = lockname($path);

//Grab the share data
if (!is_null($owner) && $owner !== '') {
	$query = \OCP\DB::prepare("SELECT X.path, Y.id FROM *PREFIX*filecache X INNER JOIN *PREFIX*storages Y ON X.storage = Y.numeric_id where X.fileid = ? LIMIT 1");
	$result = $query->execute(array($id))->fetchAll();

	$original_path = $result[0]['path'];
	$storage_id = str_replace("home::", "", $result[0]['id']) . '/';

	$lockpath = $storage_id.$original_path;
}
else $lockpath = \OCP\USER::getUser()."/files".cleanPath($path);

//Lock DB entry
$lockfile = lockname($lockpath);
$db_lock_state = \OCP\DB::prepare("SELECT * FROM *PREFIX*" . app::table . " WHERE name = ?")->execute(array($lockfile))->fetchAll();

//If lock exist, unlock it, but check the safemode
if ($db_lock_state!=null) {
	if($safe=="false") {
		$lockedby_name = $db_lock_state[0]['locked_by'];
                ShowUserName($lockedby_name);

		if( extended_precheck( $extended, $lockedby_name ) != 0 ){
			echo $l->t("No permission");
			return;
		}

		\OCP\DB::prepare("DELETE FROM *PREFIX*" . app::table . " WHERE name=?")->execute(array($lockfile));
		echo $l->t("File not locked");
	}
	else{
		$lockedby_name = $db_lock_state[0]['locked_by'];
		ShowDisplayName($lockedby_name);
		echo $l->t("Status: locked")." ".$l->t("by")." ".$lockedby_name;
	}
}
//If not locked, lock it, but check the safemode
else {
	if($safe=="false") {
		$lockedby_name = \OCP\User::getUser();
		if($naming=="rule_displayname") {
			$lockedby_name = \OCP\User::getDisplayName();
			$lockedby_name .= "|".\OCP\User::getUser();
		}

		\OCP\DB::prepare("INSERT INTO *PREFIX*".app::table."(name,locked_by) VALUES(?,?)")->execute(array($lockfile,$lockedby_name));

		//For Compatibiliy reasons, support old Lockedby type <0.8

		ShowDisplayName($lockedby_name); //Korrektur bei DisplayName
		echo $l->t("File is locked")." ".$l->t("by")." ".$lockedby_name;
	}
	else echo $l->t("Status: not locked");
}

//Functions

function lockname($path){
	//Replace all '/' with $ for a filename for the lock file
	$ppath = str_replace("/","$",cleanPath($path))."$";
	$ppath = str_replace(":","#",$ppath);

	//Remove double dollar char when exist
	if ($ppath[0] == "$" and $ppath[1] == "$") $ppath = substr($ppath,1);

	return preg_replace('/\\\/','$',$ppath);
}

function cleanPath($path){
	return preg_replace('{(/)\1+}', "/", urldecode(rtrim($path, "/")));
}

function db_fetcher( &$configkey, $configtype ){
	$type = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey=? and appid='workin2gether' LIMIT 1")->execute(array($configtype))->fetchAll();

	if (count($type) >= 1)
        	$configkey = $type[0]['configvalue'];
}

function extended_precheck( $extended, $owner ){
	//Return 0 = allowed | Return 1 = Forbidden
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

