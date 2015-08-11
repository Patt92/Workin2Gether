<?php

namespace OCA\files_w2g;

switch($_POST['action'])
{
	case 'clearall':
	\OCP\DB::prepare("TRUNCATE *PREFIX*".app::table)->execute();
	echo "clear";
	break;
	
	case 'clearthis':
	\OCP\DB::prepare("DELETE FROM *PREFIX*".app::table." WHERE name=?")->execute(array($_POST['lock']));
	echo "clear";
	break;
}

?>