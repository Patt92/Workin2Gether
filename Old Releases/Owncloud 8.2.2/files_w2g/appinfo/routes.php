<?php

namespace OCA\files_w2g\AppInfo;


$this->create('files_w2g_ajax_getcolor', 'ajax/getcolor.php')
	->actionInclude('files_w2g/ajax/getcolor.php');

$this->create('files_w2g_ajax_core', 'ajax/workin2gether.php')
	->actionInclude('files_w2g/ajax/workin2gether.php');

$this->create('files_w2g_ajax_update', 'ajax/update.php')
	->actionInclude('files_w2g/ajax/update.php');

$this->create('files_w2g_ajax_admin_db', 'ajax/admin_db.php')
	->actionInclude('files_w2g/ajax/admin_db.php');

$this->create('files_w2g_admin', 'admin.php')
	->actionInclude('files_w2g/admin.php');
