<?php

namespace OCA\workin2gether;

\OCP\Util::addTranslations('workin2gether');

$l = \OCP\Util::getL10N('workin2gether');

class app{

	const name = 'workin2gether';

	const table = 'locks_w2g';

	public static function launch()
	{
		if(\OC_User::getUser()!=false)
		{
				\OCP\Util::addScript( self::name, 'workin2gether');

				\OCP\Util::addstyle( self::name, 'styles');

				\OCP\App::registerAdmin(self::name, 'admin');
		}
	}

}

if (\OCP\App::isEnabled(app::name)) {

        if(!\OC::$server->getDatabaseConnection()->tableExists( app::table )){
			try {
                $db_exist = \OCP\DB::prepare("CREATE table *PREFIX*" . app::table . "(name varchar(255) PRIMARY KEY,created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,locked_by varchar(255))");
                $db_exist->execute();
			}catch(Exception $e) {
			}
        }

	app::launch();
}
