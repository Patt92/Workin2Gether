<?php

namespace OCA\workin2gether;

class Helpers {
    public static function decodeCharacters($string)
    {
        return html_entity_decode($string, ENT_QUOTES);
    }

    public static function encodeCharacters($string)
    {
        return htmlentities($string);
    }
}