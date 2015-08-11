<?php

namespace OCA\files_w2g;

$mode = 'color';
$color = @$_POST['color'];

if(isset($_POST['fontcolor'])) 
{	
	$mode = 'fontcolor';
	$color = $_POST['fontcolor'];
}

$l = \OCP\Util::getL10N(app::name);
$query = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey=? and appid='files_w2g' LIMIT 1");
$result = $query->execute(array($mode))->fetchAll();

if(count($result)<1)//Color already set?
{
		$query = \OCP\DB::prepare("INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) value('files_w2g',?,?)");
		$result = $query->execute(array($mode,$color));
		echo $l->t("Color has been set!");
}	
else
{
	$query = \OCP\DB::prepare("UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='files_w2g' and configkey=?");
	$result = $query->execute(array($color,$mode));
	echo $l->t("Updated successfully!");
	
	
}