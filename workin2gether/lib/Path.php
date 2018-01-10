<?php

namespace OCA\w2g2;


class Path {
    public static function getClean($path) {
        return preg_replace('{(/)\1+}', "/", urldecode(rtrim($path, "/")));
    }

    /**
     * Remove the last directory from the path.
     *
     * Ex: /files/folder1/folder2 => /files/folder1
     *
     * @param $path
     * @return bool|string
     */
    public static function removeLastDirectory($path)
    {
        return substr($path, 0, strrpos($path, '/'));
    }
}