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

//----------------------------------------------------------------------------------------------------------------------------

\OCP\Util::addscript(app::name, 'admin');
\OCP\Util::addscript(app::name, 'jscolor');
?>

<fieldset class="personalblock">
	<h2><?php p($l->t('Multi file access').' '.$l->t('configuration')) ?></h2>
	
	<?php
	p($l->t("You can choose the backcolor for locked files in the file list"));
	
	echo '
	<br><br>
	#<input id="multicolor" class="color" type="text" value="'.$value.'" style="width:180px;" name="multicolor" original-title="'.$l->t("choose a valid html color").'"></input><br><br>
	&nbsp;<input id="submitColor" type="submit" value="'.$l->t("Save background color").'" name="submitColor" original-title=""></input><br><br>
	'.$l->t("You can set an additional font color").'<br>
	#<input id="multifontcolor" class="color" type="text" value="'.$font_value.'" style="width:180px;" name="multifontcolor" original-title="'.$l->t("choose a valid html color").'"></input><br><br>
	&nbsp;<input id="submitfontcolor" type="submit" value="'.$l->t("Save font color").'" name="submitfontcolor" original-title=""></input><br><br>
	';
	?>
</fieldset>