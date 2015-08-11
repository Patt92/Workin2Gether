<?php
/*
19.02.2014 Patrick Hoffmann
This app is free to use and can be fully or in parts distributed.
If you have questions or suggestions please
feel free to contact me. patrick@gen7.de
Great thanks to Vincent Petry <pvince81@owncloud.com> for 
supporting me the whole development progress.
*/
namespace OCA\files_w2g;

//Init translations
$l = \OCP\Util::getL10N('files_w2g');

//Requirements check
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('files_w2g');

//Init Database table
$exist = \OCP\DB::prepare("SHOW TABLES LIKE '*PREFIX*".app::table."'")->execute()->fetchAll();


if($exist==null)
{
	@$query = \OCP\DB::prepare("CREATE table *PREFIX*".app::table."(name varchar(255) PRIMARY KEY,created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,locked_by varchar(255)) ".app::charset);
	@$query->execute()->fetchAll();
}
//<-End Init Database table

//Vars
$path = stripslashes($_POST['path']) ;
@$safe = @$_POST['safe'];
@$owner = @$_POST['owner'];
@$id = @$_POST['id'];
$ppath = lockname($path);

//Resolve the shared file
if(@$owner!="")
{
	$query = \OCP\DB::prepare("SELECT X.path, Y.id FROM *PREFIX*filecache X INNER JOIN *PREFIX*storages Y ON X.storage = Y.numeric_id where X.fileid = ".$id." LIMIT 1");
	$result = $query->execute()->fetchAll(); // ? with execute parameters weren't working, so I used it in prepare
	
	$original_path = $result[0]['path'];
	$storage_id = str_replace("home::","",$result[0]['id']).'/';
	
	$lockpath = $storage_id.$original_path;
}
else $lockpath = \OCP\USER::getUser()."/files".cleanPath($path);

//Lock DB entry
$lockfile = lockname($lockpath);
$db_lock_state = \OCP\DB::prepare("SELECT * FROM *PREFIX*".app::table." WHERE name = ?")->execute(array($lockfile))->fetchAll();

if ($db_lock_state!=null)
{
	if(@$safe=="false")
	{
		\OCP\DB::prepare("DELETE FROM *PREFIX*".app::table." WHERE name=?")->execute(array($lockfile));
		echo $l->t("File not locked");
	}
	else echo $l->t("Status: locked")." ".$l->t("by")." ".$db_lock_state[0]['locked_by'];
}
else
{	
	if(@$safe=="false")
	{
		\OCP\DB::prepare("INSERT INTO *PREFIX*".app::table."(name,locked_by) VALUES(?,?)")->execute(array($lockfile,\OCP\User::getUser()));
		echo $l->t("File is locked")." ".$l->t("by")." ".\OCP\User::getUser();
	}
	else echo $l->t("Status: not locked");
}

function lockname($path)
{
	//Replace all '/' with $ for a filename for the lock file
	$ppath = str_replace("/","$",cleanPath($path))."$";
	$ppath = str_replace(":","#",$ppath);
	
	//Remove double dollar char when exist
	if ($ppath[0] == "$" and $ppath[1] == "$") $ppath = substr($ppath,1);
	
	return preg_replace('/\\\/','$',$ppath);
}

function cleanPath($path) 
{
	return preg_replace('{(/)\1+}', "/", urldecode(rtrim($path, "/")));
}