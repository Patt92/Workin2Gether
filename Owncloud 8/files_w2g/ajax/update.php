<?php

namespace OCA\files_w2g;

$mode = 'color';
$value = @$_POST['color'];

if(isset($_POST['fontcolor'])) 
{	
	$mode = 'fontcolor';
	$value = $_POST['fontcolor'];
}
if(isset($_POST['suffix']))
{
	$mode = 'suffix';
	$value = $_POST['suffix'];
}

$l = \OCP\Util::getL10N(app::name);
$query = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey=? and appid='files_w2g' LIMIT 1");
$result = $query->execute(array($mode))->fetchAll();

if(count($result)<1)//Value already set?
{
		$query = \OCP\DB::prepare("INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) value('files_w2g',?,?)");
		$result = $query->execute(array($mode,$value));
		echo $l->t($mode)." ".$l->t("has been set!");
}	
else
{
	$query = \OCP\DB::prepare("UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='files_w2g' and configkey=?");
	$result = $query->execute(array($value,$mode));
	echo $l->t("Updated successfully!");
	
	
}