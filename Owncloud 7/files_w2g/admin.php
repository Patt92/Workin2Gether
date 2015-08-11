<?php
namespace OCA\files_w2g;

\OCP\User::checkAdminUser();

$tem = new \OCP\Template(app::name, 'admin');
return $tem->fetchPage();

