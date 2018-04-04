<?php

namespace OCA\w2g2\Db;

class FavoriteMapper {
    public static function getUsersForFile($fileId)
    {
        $usersIds = [];
        $categoriesResult = CategoryMapper::getCategoriesForFile($fileId);

        foreach ($categoriesResult as $category) {
            $result = CategoryMapper::getFavoriteByCategoryId($category["categoryid"]);

            if (count($result) > 0) {
                $usersIds[] = $result[0]["uid"];
            }
        }

        return $usersIds;
    }
}
