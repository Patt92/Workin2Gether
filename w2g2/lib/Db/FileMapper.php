<?php

namespace OCA\w2g2\Db;

class FileMapper {
    public static function get($fileId)
    {
        $db = \OC::$server->getDatabaseConnection();

        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE fileid = ?";

        $file = $db->executeQuery($query, [$fileId])
            ->fetch();

        if ($file && count($file) > 0) {
            return $file;
        }

        return null;
    }
}
