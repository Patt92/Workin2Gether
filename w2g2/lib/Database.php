<?php

namespace OCA\w2g2;

class Database {
    public static function fetch(&$configkey, $configtype, $_default)
    {
        $query = "SELECT * FROM *PREFIX*appconfig where configkey=? and appid='w2g2' LIMIT 1";

        $type = \OCP\DB::prepare($query)
            ->execute([$configtype])
            ->fetchAll();

        if (count($type) >= 1) {
            $configkey = $type[0]['configvalue'];
        } else {
            $configkey = $_default;
        };
    }

    public static function lockFile($fileId, $lockedByName)
    {
        $query = "INSERT INTO *PREFIX*" . app::table . "(file_id, locked_by) VALUES(?,?)";

        \OCP\DB::prepare($query)
            ->execute([$fileId, $lockedByName]);
    }

    public static function unlockFile($fileId)
    {
        $query = "DELETE FROM *PREFIX*" . app::table . " WHERE file_id=?";

        \OCP\DB::prepare($query)
            ->execute([$fileId]);
    }

    public static function getFileLock($fileId)
    {
        $query = "SELECT * FROM *PREFIX*" . app::table . " WHERE file_id = ?";

        return \OCP\DB::prepare($query)
            ->execute([$fileId])
            ->fetchAll();
    }

    public static function getUserByUsername($username)
    {
        $query = "SELECT * FROM *PREFIX*" . "users" . " WHERE uid = ?";

        return \OCP\DB::prepare($query)
            ->execute([$username])
            ->fetchAll();
    }

    public static function getUserByLDAPUsername($username)
    {
        $query = "SELECT * FROM *PREFIX*" . "accounts" . " WHERE uid = ?";

        return \OCP\DB::prepare($query)
            ->execute([$username])
            ->fetchAll();
    }

    /**
     * Get all categories for the given file, like favorites.
     *
     * @param $fileId
     * @return mixed
     */
    public static function getCategoriesForFile($fileId)
    {
        $query = "SELECT * FROM *PREFIX*" . "vcategory_to_object" . " WHERE objid = ?";

        return \OCP\DB::prepare($query)
            ->execute([$fileId])
            ->fetchAll();
    }

    /**
     * Get favorite data for the given category, like the userId
     *
     * @param $categoryId
     * @return mixed
     */
    public static function getFavoriteByCategoryId($categoryId)
    {
        $favorite = '_$!<Favorite>!$_' ;

        $query = "SELECT * FROM *PREFIX*" . "vcategory" . " WHERE category = ? AND id = ?";

        return \OCP\DB::prepare($query)
            ->execute([$favorite, $categoryId])
            ->fetchAll();
    }

    public static function getGroupFolderFile()
    {
        $groupFolderName = "__groupfolders";
        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE name = ? AND path = ?";

        return \OCP\DB::prepare($query)
            ->execute([$groupFolderName, $groupFolderName])
            ->fetchAll();
    }

    public static function getFile($fileId)
    {
        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE fileid = ?";

        return \OCP\DB::prepare($query)
            ->execute([$fileId])
            ->fetchAll();
    }
}
