<?php

use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Adapter\Directory;

/** @var TemplateEngine $this */
/** @var Snapshot[]|OptionCollection $snapshots */
/** @var array $directories */
/** @var string $urlPublic */
/** @var Directory $directory */

/**
 * @todo See if we can unify this file with src/template/Component/Backend/Snapshot/listing.php:8
 */
?>

<div id="wpstg-step-1">
    <button id="wpstg-new-snapshot" class="wpstg-next-step-link wpstg-link-btn wpstg-blue-primary wpstg-button"
            data-action="wpstg--snapshots--create">
        <?php _e('Backup & Export Database', 'wp-staging') ?>
    </button>
    <div class="wpstg--tooltip"> <?php _e('What is this?', 'wp-staging'); ?>

        <span class="wpstg--tooltiptext wpstg--tooltiptext-snapshots">
    <?php _e('This can create a backup of the WordPress database tables at a particular point in time. 
    You can restore WordPress and roll back the database to another state.<br><br>
    This is useful if you need to reset WordPress to the state before you\'ve pushed a staging site to live or if you want to revert other database changes 
    like activating a new theme or updating its settings.<br><br>
    This backup include all WordPress core tables and custom ones created by other plugins.
    Restoring a backup will not affect other staging sites or existing backups. <br><br>
    No files are included in backups! This is a quick way to roll back your site in time. For a full site backup it is recommended to use a dedicated backup plugin!
', 'wp-staging') ?>
    <p></p>
    <?php if (is_multisite()) {
        echo '<strong>' . __('Multisite Users Only: ', 'wp-staging') . '</strong>';
        echo '<p></p>';
        echo __("- If you run the backup  function on a multisite network site the snapshot will contain only the tables belonging to the particular network site. <p></p>It will not save all database tables of all network sites. So you are able to restore all network sites independently. <p></p>- If you create a snapshot on a multisite main site it will create a snapshot of <strong>all database tables</strong>.</p></p><strong>Take care:</strong> Restoring a multisite main snapshot will <strong>restore all children sites including the mainsite.</strong>", 'wp-staging');
    } ?>
        </span>
    </div>
</div>

<div id="wpstg-existing-snapshots">

    &nbsp;
    <?php

    if(!empty($snapshots))
    {
        echo '<p><strong>'.  __('Available Backups', 'wp-staging') . '</strong></p>';
    }


    foreach ($snapshots as $snapshot): ?>
        <div id="<?php echo $snapshot->getId() ?>" class="wpstg-clone wpstg-snapshot" data-type="<?php echo $snapshot->getType() ?>">

            <span class="wpstg-clone-title"><?php echo $snapshot->getName() ?></span>

            <a href="#" class="wpstg--snapshot--download wpstg-merge-clone wpstg-clone-action"
               data-id="<?php echo $snapshot->getId() ?>"
               data-url="<?php echo $snapshot->getUrlDownload() ?: null ?>"
               data-title="<?php _e('Download Backup', 'wp-staging') ?>"
               data-title-export="<?php _e('Exporting Database Tables...', 'wp-staging') ?>"
               data-btn-cancel-txt="<?php _e('CANCEL', 'wp-staging') ?>"
               data-btn-download-txt="<?php _e($snapshot->getUrlDownload() ? 'Download' : 'Export & Download', 'wp-staging') ?>"
               title="<?php _e('Download backup file on local system', 'wp-staging') ?>">
                <?php _e('Download', 'wp-staging') ?>
            </a>

            <a href="#" class="wpstg--snapshot--restore wpstg-merge-clone wpstg-clone-action"
               data-id="<?php echo $snapshot->getId() ?>"
               title="<?php _e('Restore this snapshot to your live website!', 'wp-staging') ?>">
                <?php _e('Restore', 'wp-staging') ?>
            </a>

            <a href="#" class="wpstg-remove-clone wpstg-clone-action wpstg-delete-snapshot"
               data-id="<?php echo $snapshot->getId() ?>"
               title="<?php _e('Delete this snapshot. This action can not be undone!', 'wp-staging') ?>">
                <?php _e('Delete', 'wp-staging') ?>
            </a>

            <a href="#" class="wpstg--snapshot--edit wpstg-clone-action"
               data-id="<?php echo $snapshot->getId() ?>"
               data-name="<?php echo $snapshot->getName() ?>"
               data-notes="<?php echo $snapshot->getNotes() ?>"
               title="<?php _e('Edit backup name and / or notes', 'wp-staging') ?>">
                <?php _e('Edit', 'wp-staging') ?>
            </a>

            <div class="wpstg-staging-info">
                <ul>
                    <li>
                        <strong>
                            <?php
                            _e(
                                $snapshot->getType() === Snapshot::TYPE_DATABASE ? 'Table Prefix:' : 'Id:',
                                'wp-staging'
                            )
                            ?>
                        </strong>
                        <?php echo $snapshot->getId() ?>
                    </li>
                    <li>
                        <strong><?php _e('Created on:', 'wp-staging') ?></strong>
                        <?php echo $this->transformToWpFormat($snapshot->getCreatedAt()) ?>
                        <?php if ($snapshot->getUpdatedAt()): ?>
                            &nbsp; | &nbsp;<strong><?php _e('Updated on:', 'wp-staging') ?></strong>
                            <?php echo $this->transformToWpFormat($snapshot->getUpdatedAt()) ?>
                        <?php endif ?>
                    </li>
                    <?php if ($snapshot->getNotes()): ?>
                        <li>
                            <strong><?php _e('Notes:', 'wp-staging') ?></strong><br/>
                            <?php echo nl2br($snapshot->getNotes()) ?>
                        </li>
                    <?php endif ?>
                    <?php if ($snapshot->getFilePath()): ?>
                        <li>
                            <strong><?php _e('Size:', 'wp-staging') ?></strong><br/>
                            <?php echo $snapshot->getFileSize() ?>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>
    <?php endforeach ?>
</div>

<div id="wpstg--modal--snapshot--new" data-confirmButtonText="<?php _e('Take New Database Backup', 'wp-staging') ?>" style="display: none">
    <label for="snapshot_type_database" style="display:none;"><?php _e('Snapshot Type', 'wp-staging') ?></label>
    <div style="padding: .75em; margin: 1em auto;display:none;">
        <label style="margin-right: .5em;">
            <input type="radio" name="snapshot_type" id="snapshot_type_database" value="database" checked/>
            <?php _e('Database Only', 'wp-staging') ?>
        </label>
        <label>
            <input type="radio" name="snapshot_type" id="snapshot_type_site" value="site"/>
            <?php _e('Files and Database', 'wp-staging') ?>
        </label>
    </div>
    <label for="wpstg-snapshot-name-input"><?php _e('Backup Name', 'wp-staging') ?></label>
    <input id="wpstg-snapshot-name-input" name="snapshot_name" class="swal2-input" placeholder="<?php _e('Name your backup for better distinction', 'wp-staging') ?>">
    <label for="wpstg-snapshot-notes-textarea"><?php _e('Additional Notes', 'wp-staging') ?></label>
    <textarea id="wpstg-snapshot-notes-textarea" name="snapshot_note" class="swal2-textarea" placeholder="<?php _e("Add an optional description e.g.: 'before push of staging site', 'before updating plugin XY'", 'wp-staging') ?>"></textarea>

    <div class="wpstg-advanced-options" style="text-align: left; display: none">
        <a href="#" class="wpstg--tab--toggle" data-target=".wpstg-advanced-options-site" style="text-decoration: none;">
            <span style="margin-right: .25em">►</span>
            <?php _e('Advanced Options', 'wp-staging') ?>
        </a>
        <?php _e('(click to expand)', 'wp-staging') ?>

        <div class="wpstg-advanced-options-site" style="display: none; padding-left: .75em;">
            <label style="display: block;margin: .5em 0;">
                <input type="checkbox" name="includedDirectories[]" value="<?php echo $directories['uploads'] ?>" checked/>
                <?php _e('Export Media Library', 'wp-staging') ?>
            </label>
            <label style="display: block;margin: .5em 0;">
                <input type="checkbox" name="includedDirectories[]" value="<?php echo $directories['themes'] ?>" checked/>
                <?php _e('Export Themes', 'wp-staging') ?>
            </label>
            <label style="display: block;margin: .5em 0;">
                <input type="checkbox" name="includedDirectories[]" value="<?php echo $directories['muPlugins'] ?>" checked/>
                <?php _e('Export Must-Use Plugins', 'wp-staging') ?>
            </label>
            <label style="display: block;margin: .5em 0;">
                <input type="checkbox" name="includedDirectories[]" value="<?php echo $directories['plugins'] ?>" checked/>
                <?php _e('Export Plugins', 'wp-staging') ?>
            </label>
            <label style="display: block;margin: .5em 0;">
                <input type="checkbox" name="export_database" value="true" checked/>
                <?php _e('Exporting Database', 'wp-staging') ?>
            </label>
            <input type="hidden" name="wpContentDir" value="<?php echo $directories['wpContent'] ?>"/>
            <input type="hidden" name="wpStagingDir" value="<?php echo $directories['wpStaging'] ?>"/>
            <?php unset($directories['wpContent'], $directories['wpStaging']) ?>
            <input type="hidden" name="availableDirectories" value="<?php echo implode('|', $directories) ?>"/>
        </div>
    </div>
</div>
<div id="wpstg--modal--snapshot--process" data-cancelButtonText="<?php _e('CANCEL', 'wp-staging') ?>" style="display: none">
    <span class="wpstg-loader"></span>
    <h3 class="wpstg--modal--process--title" style="color: #a8a8a8;margin: .25em 0;">
        <?php _e('Processing...', 'wp-staging') ?>
    </h3>
    <div style="margin: .5em 0; color: #a8a8a8;">
        <?php
        echo sprintf(
            __('Progress %s - Elapsed time %s', 'wp-staging'),
            '<span class="wpstg--modal--process--percent">0</span>%',
            '<span class="wpstg--modal--process--elapsed-time">0:00</span>'
        )
        ?>
    </div>
    <div class="wpstg--modal--process--generic-problem"></div>
    <button
            class="wpstg--modal--process--logs--tail"
            data-txt-bad="<?php echo sprintf(
                __('(%s) Critical, (%s) Errors, (%s) Warnings. Show Logs', 'wp-staging'),
                '<span class=\'wpstg--modal--logs--critical-count\'>0</span>',
                '<span class=\'wpstg--modal--logs--error-count\'>0</span>',
                '<span class=\'wpstg--modal--logs--warning-count\'>0</span>'
            ) ?>"
    >
        <?php _e('Show Logs', 'wp-staging') ?>
    </button>
    <div class="wpstg--modal--process--logs"></div>
</div>
<div id="wpstg--modal--snapshot--download" style="display: none">
    <h2>{title}</h2>
    <div class="wpstg--modal--download--logs--wrapper" style="display:none">
        <button class="wpstg--modal--process--logs--tail">{btnTxtLog}</button>
        <div class="wpstg--modal--process--logs"></div>
    </div>
</div>
<div
        id="wpstg--modal--snapshot--import"
        data-confirmButtonText="<?php _e('IMPORT', 'wp-staging') ?>"
        data-nextButtonText="<?php _e('NEXT', 'wp-staging') ?>"
        data-cancelButtonText="<?php _e('CANCEL', 'wp-staging') ?>"
        data-baseDirectory="<?php echo $directory->getPluginUploadsDirectory() ?>"
        style="display: none"
>
    <h2 class="wpstg--modal--snapshot--import--upload--title"><?php _e('Import Snapshot', 'wp-staging') ?></h2>
    <div style="padding: .75em; margin: 1em auto;">
        <div class="wpstg--modal--snapshot--import--upload">
            <div class="wpstg--modal--snapshot--import--upload--container">
                <div class="wpstg--uploader">
                    <input type="file" name="wpstg--snapshot--import--upload--file"/>
                    <img src="<?php echo $urlPublic . 'img/upload.svg' ?>" alt="Upload Image"/>
                    <span class="wpstg--snapshot--import--selected-file"></span>
                    <span class="wpstg--drag-or-upload">
            <?php _e('Drag a new export file here or choose another option', 'wp-staging') ?>
          </span>
                    <span class="wpstg--drag">
            <?php _e('Drag and Drop a snapshot file to start import', 'wp-staging') ?>
          </span>
                    <span class="wpstg--drop">
            <?php _e('Drop export file here', 'wp-staging') ?>
          </span>
                    <div class="wpstg--snapshot--import--options">
                        <button
                                class="wpstg-blue-primary wpstg-button wpstg-link-btn wpstg--snapshot--import--choose-option"
                                data-txtOther="<?php _e('Import from', 'wp-staging') ?>"
                                data-txtChoose="<?php _e('Choose an Option', 'wp-staging') ?>"
                        >
                            <?php _e('Import from', 'wp-staging') ?>
                        </button>
                        <ul>
                            <li>
                                <button class="wpstg--snapshot--import--option wpstg-blue-primary" data-option="file">
                                    <?php _e('Local Computer', 'wp-staging') ?>
                                </button>
                            </li>
                            <li>
                                <button class="wpstg--snapshot--import--option wpstg-blue-primary" data-option="filesystem">
                                    <?php _e('Upload Directory', 'wp-staging') ?>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="wpstg--modal--import--upload--process">
                    <div class="wpstg--modal--import--upload--progress"></div>
                    <h4 class="wpstg--modal--import--upload--progress--title">
                        <?php echo sprintf(__('Uploading %s%%...', 'wp-staging'), '<span></span>') ?>
                    </h4>
                </div>
            </div>
            <div
                    class="wpstg--modal--snapshot--import--upload--status"
                    data-txt-uploading="<?php _e('Uploading...', 'wp-staging') ?>"
                    data-txt-done="<?php _e('Uploaded Successfully', 'wp-staging') ?>"
                    data-txt-error="<?php _e('Error! {message}', 'wp-staging') ?>"
            >
            </div>
        </div>
        <div class="wpstg--modal--snapshot--import--filesystem">
            <button class="wpstg--snapshot--import--option wpstg-blue-primary" data-option="upload">
                <?php _e('GO BACK', 'wp-staging') ?>
            </button>
            <div style="margin-top: .25em;font-size:14px;">
                <?php
                echo __('Upload import file to server directory:', 'wp-staging') . '<br>';
                echo $directory->getPluginUploadsDirectory();
                ?>
            </div>
            <ul></ul>
        </div>
        <div class="wpstg--modal--snapshot--import--search-replace--wrapper">
            <div class="wpstg--modal--snapshot--import--search-replace--info">
                <p><?php _e('Search & Replace strings in the database. (Fully optional).', 'wp-staging') ?></p>
                <p><?php _e('Leave empty and WP Staging handles this automatically.', 'wp-staging') ?></p>
            </div>
            <div class="wpstg--modal--snapshot--import--search-replace--input--container">
                <div class="wpstg--modal--snapshot--import--search-replace--input-group">
                    <input name="wpstg__snapshot__import__search[{i}]" data-index="{i}" class="wpstg--snapshot--import--search" placeholder="Search"/>
                    <input name="wpstg__snapshot__import__replace[{i}]" data-index="{i}" class="wpstg--snapshot--import--replace" placeholder="Replace"/>
                </div>
            </div>
            <button class="wpstg--modal--snapshot--import--search-replace--new"><?php _e('+', 'wp-staging') ?></button>
        </div>
    </div>
</div>

<div
        id="wpstg--js--translations"
        style="display:none;"
        data-modal-txt-critical="<?php _e('Critical', 'wp-staging') ?>"
        data-modal-txt-errors="<?php _e('Error(s)', 'wp-staging') ?>"
        data-modal-txt-warnings="<?php _e('Warning(s)', 'wp-staging') ?>"
        data-modal-txt-and="<?php _e('and', 'wp-staging') ?>"
        data-modal-txt-found="<?php _e('Found', 'wp-staging') ?>"
        data-modal-txt-show-logs="<?php _e('Show Logs', 'wp-staging') ?>"
        data-modal-logs-title="<?php _e(
            '{critical} Critical, {errors} Error(s) and {warnings} Warning(s) Found',
            'wp-staging'
        ) ?>"
></div>

<div id="wpstg-delete-confirmation"></div>
