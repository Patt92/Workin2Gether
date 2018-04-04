<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class ConfigMapper {
    public static function getColor()
    {
        $defaultColor = "008887";
        $color = static::get('color');

        return $color ?: $defaultColor;
    }

    public static function getFontColor()
    {
        $defaultFontColor = "FFFFFF";
        $fontColor = static::get('fontcolor');

        return $fontColor ?: $defaultFontColor;
    }

    public static function getDirectoryLock()
    {
        $default = "directory_locking_all";
        $value = static::get('directory_locking');

        return $value ?: $default;
    }

    public static function getLockingByNameRule()
    {
        $default = "rule_username";
        $value = static::get('suffix');

        return $value ?: $default;
    }

    protected static function get($configKey)
    {
        $db = \OC::$server->getDatabaseConnection();
        $appName = 'w2g2';

        $query = "SELECT * FROM *PREFIX*appconfig where configkey=? and appid=? LIMIT 1";

        $result = $db->executeQuery($query, [$configKey, $appName]);
        $row = $result->fetch();

        return $row ? $row['configvalue'] : '';
    }

    public static function store($type, $value)
    {
        $db = \OC::$server->getDatabaseConnection();
        $query = "INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) VALUES('w2g2',?,?)";

        $db->executeQuery($query, [$type, $value]);
    }

    public static function update($type, $value)
    {
        $db = \OC::$server->getDatabaseConnection();
        $query = "UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='w2g2' and configkey=?";

        $db->executeQuery($query, [$value, $type]);
    }
}
