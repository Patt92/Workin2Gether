<?php

namespace OCA\files_w2g;

$value = "008887"; 		//Default color, like it or hate it
$font_value = "FFFFFF"; //Default font-color

//-------------------------------------------- Get colors from database ------------------------------------------------------

//backcolor
$query = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey='color' and appid='files_w2g' LIMIT 1");
$result = $query->execute()->fetchAll();
if(count($result)>=1)
		$value = $result[0]['configvalue'];

//fontcolor
$query = \OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey='fontcolor' and appid='files_w2g' LIMIT 1");
$result = $query->execute()->fetchAll();
if(count($result)>=1)
		$font_value = $result[0]['configvalue'];
		
//Locked files
$exist = \OCP\DB::prepare("SHOW TABLES LIKE '*PREFIX*".app::table."'")->execute()->fetchAll();
if($exist!=null)
	$result = \OCP\DB::prepare("SELECT * FROM *PREFIX*".app::table)->execute()->fetchAll();
//----------------------------------------------------------------------------------------------------------------------------

\OCP\Util::addscript(app::name, 'admin');
\OCP\Util::addscript(app::name, 'jscolor');
?>
<div class="section" id="multiaccess_settings">
<fieldset class="personalblock">
	<h2><?php p($l->t('Manage multiaccess')) ?></h2>
	
	<?php
	p($l->t("Here you can set the colors for the locked files."));
	
	echo '
	<br><br>
	'.$l->t("Background color").':<br>
	#<input id="multicolor" class="color" type="text" value="'.$value.'" style="width:180px;" name="multicolor" original-title="'.$l->t("choose a valid html color").'"></input>
	&nbsp;<input id="submitColor" type="submit" value="'.$l->t("Save").'" name="submitColor" original-title=""></input><br><br>
	
	'.$l->t("Font color").':<br>
	#<input id="multifontcolor" class="color" type="text" value="'.$font_value.'" style="width:180px;" name="multifontcolor" original-title="'.$l->t("choose a valid html color").'"></input>
	&nbsp;<input id="submitfontcolor" type="submit" value="'.$l->t("Save").'" name="submitfontcolor" original-title=""></input><br><br>
	';
	
	if(count($result)>0)
	{
	echo '<div id="lockfield">'.$l->t("Locked files").':<br>	
		<select multiple="multiple" size="6" style="height:100px;" id="select_lock">';
		for($i=1;$i<=count($result);$i++)
			echo '<option value="'.$result[$i-1]['name'].'">'.rtrim(str_replace('$','/',$result[$i-1]['name']),'/').'</option>';
		echo '</select><br><input id="clearthis" type="submit" value="'.$l->t("Unlock this file").'" name=clearthis"></input>
		<input id="clearall" type="submit" value="'.$l->t("Unlock all files").'" name=clearall"></input></div>
		';
	}
	else
		echo $l->t("There are no locked files at the moment");
	?>
</fieldset></div>