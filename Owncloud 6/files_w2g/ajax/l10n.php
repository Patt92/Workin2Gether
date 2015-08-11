<?php
$l = \OCP\Util::getL10N('files_w2g');
if($l->t($_POST['rawtext'])!="") echo $l->t($_POST['rawtext']); else echo $_POST['rawtext'];