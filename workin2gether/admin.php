<?php

namespace OCA\workin2gether;

\OCP\User::checkAdminUser();

$tem = new \OCP\Template(app::name, 'admin');
return $tem->fetchPage();

