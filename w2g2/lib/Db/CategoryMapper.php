<?php

namespace OCA\w2g2\Db;

class CategoryMapper {
    /**
     * Get favorite data for the given category, like the userId
     *
     * @param $categoryId
     * @return mixed
     */
    public static function getFavoriteByCategoryId($categoryId)
    {
        $db = \OC::$server->getDatabaseConnection();
        $favorite = '_$!<Favorite>!$_' ;

        $query = "SELECT * FROM *PREFIX*" . "vcategory" . " WHERE category = ? AND id = ?";

        return $db->executeQuery($query, [$favorite, $categoryId])
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
        $db = \OC::$server->getDatabaseConnection();
        $query = "SELECT * FROM *PREFIX*" . "vcategory_to_object" . " WHERE objid = ?";

        return $db->executeQuery($query, [$fileId])
            ->fetchAll();
    }
}
