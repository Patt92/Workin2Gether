<?php

namespace OCA\w2g2;

\OCP\User::checkAdminUser();

$tem = new \OCP\Template(app::name, 'admin');
return $tem->fetchPage();
