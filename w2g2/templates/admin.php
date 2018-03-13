<?php

namespace OCA\w2g2;

//backcolor
$value = "008887";
db_fetcher($value, "color");

//fontcolor
$font_value = "FFFFFF";
db_fetcher($font_value, "fontcolor");

//Locked by name rule
$naming = "rule_username";
db_fetcher($naming, "suffix");

// Directories locking
$directoryLocking = "directory_locking_all";
db_fetcher($directoryLocking, "directory_locking");

//Locked files
if (\OC::$server->getDatabaseConnection()->tableExists(app::table)) {
    $query = "SELECT f.path, f.fileid, l.locked_by FROM *PREFIX*" . app::table . " AS l JOIN *PREFIX*" . 'filecache' . " as f ON l.file_id = f.fileid";

    $lockedFiles = \OCP\DB::prepare($query)
        ->execute()
        ->fetchAll();

    $groupFolderName = "__groupfolders/";
    $fileName = 'files/';
    $groupFolderQuery = "SELECT mount_point FROM *PREFIX*" . 'group_folders' . " WHERE folder_id=?";

    for ($i = 0; $i < count($lockedFiles); $i++) {
        $groupFolderIndex = strpos($lockedFiles[$i]['path'], $groupFolderName);
        $fileIndex = strpos($lockedFiles[$i]['path'], $fileName);

        if ($groupFolderIndex === 0) {
            $path = substr($lockedFiles[$i]['path'], strlen($groupFolderName));

            $slashIndex = strpos($path, '/');

            $groupFolderId = substr($path, 0, $slashIndex);
            $file = substr($path, $slashIndex + 1);

            $result = \OCP\DB::prepare($groupFolderQuery)
                ->execute([$groupFolderId])
                ->fetchAll();

            if ($result && count($result) > 0) {
                $path = $result[0]['mount_point'];

                $lockedFiles[$i]['path'] = $path . '/' . $file;
            }
        } else if ($fileIndex === 0) {
            $filePath = substr($lockedFiles[$i]['path'], strlen('files/'));
            $lockedFiles[$i]['path'] = $lockedFiles[$i]['locked_by'] . '/' . $filePath;
        }
    }
}

\OCP\Util::addscript(app::name, 'admin');
\OCP\Util::addscript(app::name, 'jscolor');

?>

<div class="section" id="multiaccess_settings">
    <fieldset class="personalblock">
        <h2><?php p($l->t('Manage multiaccess')) ?></h2>

        <?php
        p($l->t("Here you can set the colors for the locked files."));

        echo '
	<br><br>
	<u>' . $l->t("Background color") . ':</u><br>
	#<input id="multicolor" class="color" type="text" value="' . $value . '" style="width:180px;" name="multicolor" original-title="' . $l->t("choose a valid html color") . '"></input>
	&nbsp;<input id="submitColor" type="submit" value="' . $l->t("Save") . '" name="submitColor" original-title=""></input><br><br>

	<u>' . $l->t("Font color") . ':</u><br>
	#<input id="multifontcolor" class="color" type="text" value="' . $font_value . '" style="width:180px;" name="multifontcolor" original-title="' . $l->t("choose a valid html color") . '"></input>
	&nbsp;<input id="submitfontcolor" type="submit" value="' . $l->t("Save") . '" name="submitfontcolor" original-title=""></input><br><br>
	';

        if (count($lockedFiles) > 0) {
            echo '<div id="lockfield"><u>' . $l->t("Locked files") . ':</u><br>

            <select size="6" style="height:100px; min-width: 400px;" id="select_lock">';
            for ($i = 0; $i < count($lockedFiles); $i++)
                echo '<option value="' . $lockedFiles[$i]['fileid'] . '">' . rtrim($lockedFiles[$i]['path'], '/') . '</option>';
            echo '
            </select>
    
            <br>
            
            <input id="clearthis" type="submit" value="' . $l->t("Unlock this file") . '" name=clearthis"></input>
            
            <input id="clearall" type="submit" value="' . $l->t("Unlock all files") . '" name=clearall"></input></div>
            ';
        } else {
            echo $l->t("There are no locked files at the moment");
        }

        echo '<br>';
        //Suffix section
        echo '<br><u>' . $l->t("'Locked by' suffix") . ':</u><br>
	<p style="font-size:10px">(' . $l->t("Old file locks won't be affected by this") . ')</p><br>
	<div>
		<p><input id="rule_username" type="radio" name="suffix" ';

        if ($naming == "rule_username") echo 'checked="checked"';
        echo '><label for="rule_username">' . $l->t("username") . '</label><br><em>' . $l->t("Shows the real username i.e. admin, or LDAP UUID") . '</em></input></p>
		<p><input id="rule_displayname" type="radio" name="suffix" ';
        if ($naming == "rule_displayname") echo 'checked="checked"';
        echo '><label for="rule_displayname">' . $l->t("display name") . '</label><br><em>' . $l->t("Shows the full displayed name, i.e. John Doe") . '</em></input></p>
	</div>
	';

        // Locking a directory section
        echo '
        <br>
        
        <u>Directory locking:</u>
        
        <br>
        
        <p> Here you can set what should happen when a user locks a directory. </p>
        
        <br>
        
        <div>
            <p>
                <input id="directory_locking_all" 
                       type="radio" 
                       name="directory_locking" ';
        if ($directoryLocking == "directory_locking_all") echo 'checked="checked"';
        echo '>
    
                    <label for="directory_locking_all"> 
                        lock everything below the directory (including files, directories, subdirectories, etc.) 
                    </label>
                </input>
            </p>
            
            <p>
                <input id="directory_locking_files" 
                       type="radio" 
                       name="directory_locking" ';
        if ($directoryLocking == "directory_locking_files") echo 'checked="checked"';
        echo '>
    
                    <label for="directory_locking_files"> lock only the files in the directory </label>
                </input>
            </p>
            
            <p>
                <input id="directory_locking_none" 
                       type="radio" 
                       name="directory_locking" ';
        if ($directoryLocking == "directory_locking_none") echo 'checked="checked"';
        echo '>
    
                    <label for="directory_locking_none"> disable for directories </label>
                </input>
            </p>
        </div>
	';
        ?>
    </fieldset>
</div>

<?php

function db_fetcher(&$configkey, $configtype)
{
    $query = "SELECT * FROM *PREFIX*appconfig where configkey=? and appid='w2g2' LIMIT 1";

    $type = \OCP\DB::prepare($query)
        ->execute(array($configtype))
        ->fetchAll();

    if (count($type) >= 1)
        $configkey = $type[0]['configvalue'];
}

?>
