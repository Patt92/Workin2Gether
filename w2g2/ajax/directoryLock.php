<?php

//\OCP\JSON::checkLoggedIn(); Fix 401 unauthorized error. This script may be executed without login!
\OCP\JSON::callCheck();

$query = @OCP\DB::prepare("SELECT * FROM *PREFIX*appconfig where configkey=? and appid='w2g2' LIMIT 1");
$result = @$query->execute(array('directory_locking'))->fetchAll();

if (count($result) >= 1) {
    echo $result[0]['configvalue'];
} else {
    echo "";
}
