<?php

namespace OCA\w2g2\Db;

class GroupFolderMapper {
    public static function get()
    {
        $groupFolderName = "__groupfolders";
        $db = \OC::$server->getDatabaseConnection();

        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE name = ? AND path = ?";

        $results = $db->executeQuery($query, [$groupFolderName, $groupFolderName])
                ->fetchAll();

        if (count($results) > 0) {
            return $results[0]['fileid'];
        }

        return null;
    }

    public static function getMountPoints($folderId)
    {
        $db = \OC::$server->getDatabaseConnection();

        $query = "SELECT mount_point FROM *PREFIX*" . 'group_folders' . " WHERE folder_id=?";

        return $db->executeQuery($query, [$folderId])
            ->fetchAll();
    }
}
