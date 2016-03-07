<?php

namespace OCA\files_w2g;

\OCP\Util::addTranslations('files_w2g');

$l = \OCP\Util::getL10N('files_w2g');

class app{

	const name = 'files_w2g';

	const table = 'locks_w2g';

	public static function launch()
	{

		\OCP\Util::addScript( self::name, 'workin2gether');

		\OCP\Util::addstyle( self::name, 'styles');

		\OCP\App::registerAdmin(self::name, 'admin');

	}

}

if (\OCP\App::isEnabled(app::name)) {

	//Check if DB exist, otherwise create it
        if(!\OC::$server->getDatabaseConnection()->tableExists( app::table )){
			try {
                $db_exist = \OCP\DB::prepare("CREATE table *PREFIX*" . app::table . "(name varchar(255) PRIMARY KEY,created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,locked_by varchar(255))");
                $db_exist->execute();
			}catch(Exception $e) {	
			}
        }

	app::launch();
}
