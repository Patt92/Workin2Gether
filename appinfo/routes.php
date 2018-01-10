<?php

namespace OCA\w2g2\AppInfo;


$this->create('w2g2_ajax_getcolor', 'ajax/getcolor.php')
    ->actionInclude('w2g2/ajax/getcolor.php');

$this->create('w2g2_ajax_core', 'ajax/w2g2.php')
    ->actionInclude('w2g2/ajax/w2g2.php');

$this->create('w2g2_ajax_update', 'ajax/update.php')
    ->actionInclude('w2g2/ajax/update.php');

$this->create('w2g2_ajax_admin_db', 'ajax/admin_db.php')
    ->actionInclude('w2g2/ajax/admin_db.php');

$this->create('w2g2_admin', 'admin.php')
    ->actionInclude('w2g2/admin.php');

$this->create('w2g2_ajax_directoryLock', 'ajax/directoryLock.php')
    ->actionInclude('w2g2/ajax/directoryLock.php');
