<?php

namespace OCA\workin2gether\AppInfo;


$this->create('workin2gether_ajax_getcolor', 'ajax/getcolor.php')
	->actionInclude('workin2gether/ajax/getcolor.php');

$this->create('workin2gether_ajax_core', 'ajax/workin2gether.php')
	->actionInclude('workin2gether/ajax/workin2gether.php');

$this->create('workin2gether_ajax_update', 'ajax/update.php')
	->actionInclude('workin2gether/ajax/update.php');

$this->create('workin2gether_ajax_admin_db', 'ajax/admin_db.php')
	->actionInclude('workin2gether/ajax/admin_db.php');

$this->create('workin2gether_admin', 'admin.php')
	->actionInclude('workin2gether/admin.php');

$this->create('workin2gether_ajax_directoryLock', 'ajax/directoryLock.php')
    ->actionInclude('workin2gether/ajax/directoryLock.php');
