<?php

namespace OCA\files_w2g;

$l = \OCP\Util::getL10N('files_w2g');

class app{

	const name = 'files_w2g';
	
	const table = 'locks_w2g';
	
	const charset = 'DEFAULT CHARACTER SET=utf8 DEFAULT COLLATE=utf8_bin';
	
	public static function launch()
	{
		\OCP\Util::addscript( self::name, 'workin2gether');
		
		\OCP\Util::addstyle( self::name, 'styles');
		
		\OCP\App::registerAdmin(self::name, 'admin');	
	}
}

if (\OCP\App::isEnabled(app::name)) {
	app::launch();
}