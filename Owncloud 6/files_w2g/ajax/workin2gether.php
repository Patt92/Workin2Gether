<?php

/*
19.02.2014 Patrick Hoffmann
This app is free to use and can fully or parts of
it distributed. If you have questions or suggestions please
feel free to contact me. patrick@gen7.de
Great thanks to Vincent Petry <pvince81@owncloud.com> for 
supporting me the whole development progress.
*/

//Init translations
$l = \OCP\Util::getL10N('files_w2g');

//Requirements check
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('files_w2g');


//Default path for lock files

$root = \OC::$SERVERROOT;//$_SERVER['DOCUMENT_ROOT'];

$apath = $root.OCP\Util::linkTo('files_w2g', 'lock')."/";

if(!@file_exists($root.OCP\Util::linkTo('files_w2g')."/lock"))
	@mkdir($root.OCP\Util::linkTo('files_w2g')."/lock", 0777, true);

$storage = preg_replace('{/$}', '', \OC_Config::getValue('datadirectory',\OC::$SERVERROOT . '/data'))."/";


//Vars
$path = stripslashes($_POST['path']) ;
@$safe = @$_POST['safe'];
$ppath = lockname($path);


if(cleanPath($path)=="/Shared"){ echo "Forbidden"; return 1; } //Doesn't make sense to lock it cause you can't share it

if(substr(cleanPath($path),0,7)=="/Shared")
{
	// /Shared/...
	$relpath = explode('/',substr(cleanPath($path),7,strlen($path)-7));
	$relative_path = '/'.$relpath[count($relpath)-1];

	$backcount = 0;
	$dbg = $relpath[count($relpath)-1-0];
	do
	{
		$query = OCP\DB::prepare("SELECT X.parent, X.id, X.uid_owner, Y.path FROM *PREFIX*share X INNER JOIN *PREFIX*filecache Y ON X.file_source = Y.fileid where X.share_with = ? and X.file_target = ? LIMIT 1");
		$result = $query->execute(array(OCP\USER::getUser(),'/'.$relpath[count($relpath)-1-$backcount]))->fetchAll();
		$backcount+=1;
	}while(count($result)<1 && $backcount<count($relpath));
		
	if ($backcount>0) $backcount-=1;

	
	
	
	$user = $result[0]['uid_owner'];
	$postpath = $result[0]['path'];
	//get the original share user name
	
	$old = ""; $new = "-";
	while ($new!="")
	{
		$old = $new;
		$query = OCP\DB::prepare("SELECT parent, uid_owner, id FROM *PREFIX*share where id = ? LIMIT 1");
		$result = $query->execute(array($result[0]['parent']))->fetchAll();
		$new = @$result[0]['uid_owner'];
	}
	if($old!="-") $user = $old;
	
	
	$realpath = $storage.$user.'$'.$postpath;
	
	for($i=$backcount;$i>0;$i-=1)
	{
		$realpath .= '/'.$relpath[count($relpath)-$i];
	}
	
	
	
}
else $realpath = $storage.OCP\USER::getUser()."/files".cleanPath($path);

//lockfile name
$lock = $apath.lockname($realpath);

if (file_exists($lock))
{
	if(@$safe=="false")
	{
		@unlink($lock);
		echo $l->t("File not locked");
	}
	else echo $l->t("Status: locked");
}
else
{
	@date_default_timezone_set('Europe/Berlin');
	
	if(@$safe=="false")
	{
		$h = fopen($lock, "w");
		fwrite($h,OCP\User::getUser()." ".date(DATE_RFC822));
		fclose($h);
		echo $l->t("File is locked");
	}
	else echo $l->t("Status: not locked");
}



function lockname($path)
{
	//Replace all '/' with $ for a filename for the lock file
	$ppath = str_replace("/","$",cleanPath($path))."$";
	
	$ppath = str_replace("Shared","",$ppath);
	$ppath = str_replace(":","#",$ppath);
	
	//Remove double dollar char when exist
	if ($ppath[0] == "$" and $ppath[1] == "$") $ppath = substr($ppath,1);
	
	return preg_replace('/\\\/','$',$ppath);

}

function cleanPath($path) {
    
        $path = rtrim($path, "/");
        $path = urldecode($path);
		return preg_replace('{(/)\1+}', "/", $path);
}
