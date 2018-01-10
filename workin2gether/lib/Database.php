<?php

namespace OCA\workin2gether;

class Database {
    public static function fetch(&$configkey, $configtype, $_default)
    {
        $query = "SELECT * FROM *PREFIX*appconfig where configkey=? and appid='workin2gether' LIMIT 1";

        $type = \OCP\DB::prepare($query)
            ->execute(array($configtype))
            ->fetchAll();

        if (count($type) >= 1) {
            $configkey = $type[0]['configvalue'];
        } else {
            $configkey = $_default;
        };
    }

    public static function lockFile($file, $lockedByName)
    {
        $query = "INSERT INTO *PREFIX*" . app::table . "(name, locked_by) VALUES(?,?)";

        \OCP\DB::prepare($query)
            ->execute(array($file, $lockedByName));
    }

    public static function unlockFile($lockedFile)
    {
        $query = "DELETE FROM *PREFIX*" . app::table . " WHERE name=?";

        \OCP\DB::prepare($query)
            ->execute(array($lockedFile));
    }

    public static function getFileLock($file) {
        $query = "SELECT * FROM *PREFIX*" . app::table . " WHERE name = ?";

        return \OCP\DB::prepare($query)
            ->execute(array($file))
            ->fetchAll();
    }
}