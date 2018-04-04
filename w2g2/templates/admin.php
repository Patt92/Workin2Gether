<?php
/** @var array $_ */

script($_['appName'], 'admin');
script($_['appName'], 'jscolor');

?>

<div class="section" id="multiaccess_settings">
    <fieldset class="personalblock">
        <h2> <?php p($l->t('Manage file locks')) ?> </h2>

        <?php p($l->t("Here you can set the colors for the locked files.")); ?>

        <br>
        <br>

        <div>
            <label for="multicolor"> <?php p($l->t("Background color")) ?>:</label>

            <br>

            # <input id="multicolor"
                     class="color"
                     type="text"
                     value="<?php p($_['color']) ?>"
                     style="width:180px;"
                     name="multicolor"
                     original-title="<?php p($l->t("choose a valid html color")) ?>">

            &nbsp;

            <input id="submitColor"
                   type="submit"
                   value="<?php p($l->t("Save")) ?>"
                   name="submitColor">
        </div>

        <br>
        <br>

        <div>
            <label for="multifontcolor"> <?php p($l->t("Font color")) ?>:</label>

            <br>

            # <input id="multifontcolor"
                     class="color"
                     type="text"
                     value="<?php p($_['fontColor']) ?>"
                     style="width:180px;"
                     name="multifontcolor"
                     original-title="<?php p($l->t("choose a valid html color")) ?>">

            &nbsp;

            <input id="submitfontcolor"
                   type="submit"
                   value="<?php p($l->t("Save")) ?>"
                   name="submitfontcolor">
        </div>

        <br>
        <br>

        <?php
            if (count($_['lockedFiles']) > 0) {
        ?>

        <div id="lockfield">
            <label for="select_lock"> <?php p($l->t("Locked files")) ?>: </label>

            <br>

            <select size="6"
                    style="height:100px; min-width: 400px;"
                    id="select_lock">'

                <?php for ($i = 0; $i < count($_['lockedFiles']); $i++) { ?>
                    <option value="<?php p($_['lockedFiles'][$i]['fileid']) ?>">
                        <?php p(rtrim($_['lockedFiles'][$i]['path'], '/')) ?>
                    </option>
                <?php } ?>
            </select>

            <br>

            <input id="clearthis"
                   type="submit"
                   value="<?php p($l->t("Unlock this file")) ?>"
                   name=clearthis">

            <input id="clearall"
                   type="submit"
                   value="<?php p($l->t("Unlock all files")) ?>"
                   name=clearall">
        </div>

        <?php
            } else {
                p($l->t("There are no locked files at the moment"));
            }
        ?>

        <br>
        <br>

        <u> <?php p($l->t("'Locked by' suffix")) ?>: </u>

        <br>

        <div id="suffix-section">
            <p style="font-size:10px">
                (<?php p($l->t("Old file locks won't be affected by this")) ?>)
            </p>

            <br>

            <div>
                <p>
                    <input id="rule_username"
                           type="radio"
                           name="suffix"
                           <?php
                                if ($_['lockingByUsername']) {
                                    p('checked');
                                }
                            ?>
                    >

                    <label for="rule_username">
                        <?php p($l->t("username")); ?>
                    </label>

                    <br>

                    <em>
                        <?php p($l->t("Shows the real username i.e. admin, or LDAP UUID")); ?>
                    </em>
                </p>

                <p>
                    <input id="rule_displayname"
                           type="radio" 
                           name="suffix"
                           <?php
                                if ($_['lockingByDisplayName']) {
                                    p('checked');
                                }
                           ?>
                    >

                    <label for="rule_displayname">
                        <?php p($l->t("display name")) ?>
                    </label>

                    <br>

                    <em>
                        <?php p($l->t("Shows the full displayed name, i.e. John Doe")) ?>
                    </em>
                </p>
            </div>
        </div>

        <br>

        <div id="directory-lock">
            <u> Directory locking: </u>

            <br>

            <p> Here you can set what should happen when a user locks a directory. </p>

            <br>

            <div>
                <p>
                    <input id="directory_locking_all"
                           type="radio"
                           name="directory_locking"
                           <?php if ($_['directoryLockingAll']) {
                               p('checked');
                           } ?>
                    >

                    <label for="directory_locking_all">
                        lock everything below the directory (including files, directories, subdirectories, etc.)
                    </label>
                </p>

                <p>
                    <input id="directory_locking_files"
                           type="radio"
                           name="directory_locking"
                           <?php if ($_['directoryLockingFiles']) {
                               p('checked');
                           } ?>
                    >

                    <label for="directory_locking_files">
                        lock only the files in the directory
                    </label>
                </p>

                <p>
                    <input id="directory_locking_none"
                           type="radio"
                           name="directory_locking"
                           <?php if ($_['directoryLockingNone']) {
                               p('checked');
                           } ?>
                    >

                    <label for="directory_locking_none">
                        disable for directories
                    </label>
                </p>
            </div>
        </div>
    </fieldset>
</div>
