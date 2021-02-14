<div class="wpstg-tabs-wrapper">
    <a href="#" class="wpstg-tab-header active" data-id="#wpstg-scanning-db" style="display:block;">
        <span class="wpstg-tab-triangle">&#9658;</span>
        <?php echo __("Database Tables", "wp-staging") ?>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-db">
        <?php do_action("wpstg_scanning_db") ?>
        <h4 style="margin:0">
            <?php echo __("Select tables to push to production website", "wp-staging");
            ?>
        </h4>
        <p>
            <?php echo __("<strong style='color:red;'>Note:</strong> Your database table selection is stored automatically<br/>and will be used as default selection for the next push.", "wp-staging"); ?>
        </p>
        <p>
            <strong>
                <?php
                $db = empty($options->databaseDatabase) ? DB_NAME : $options->databaseDatabase;
                echo __("Database: ", "wp-staging") . $db;
                echo '<br/>';
                echo __("Table Prefix: ", "wp-staging") . $options->prefix;
                ?>
            </strong>
        </p>
        <a href="#" class="wpstg-button-unselect button"
           style="margin-bottom:10px;"> <?php _e('Unselect All', 'wp-staging'); ?> </a>
        <?php
        if (isset($options->tables)) {
            foreach ($options->tables as $table):

                $tableWithoutPrefix = wpstg_replace_first_match($options->prefix, '', $table->name);

                if (!is_main_site()) {
                    // table name without prefix must begin with "int_" if it is either no multisite main site or no regular single wordpress site or when it is a multisite child site e.g 4_options
                    $attributes = in_array($table->name, $options->excludedTables) ? '' : "checked";
                } else {
                    // table name without prefix must begin with "string" if it is a regular single WordPress site or a WordPress multisite main site e.g options
                    preg_match('/^\D*/', $tableWithoutPrefix, $match);
                    $attributes = !array_filter($match) ? '' : "checked";
                }

                // Check if table has been stored in excluded array for further pushing threads
                if (in_array($table->name, $options->excludedTables)) {
                    $attributes = '';
                }

                ?>
                <div class="wpstg-db-table">
                    <label>
                        <input class="wpstg-db-table-checkboxes" type="checkbox"
                               name="<?php echo $table->name ?>" <?php echo $attributes ?>>
                        <?php echo $table->name ?>
                    </label>
                    <span class="wpstg-size-info">
                        <?php echo $scan->formatSize($table->size) ?>
                    </span>
                </div>
            <?php
            endforeach;
        }
        ?>
        <div>
            <a href="#" class="wpstg-button-unselect button"
               style="margin-top:10px;"> <?php _e('Unselect All', 'wp-staging'); ?> </a>
        </div>
    </div>

    <a href="#" class="wpstg-tab-header" data-id="#wpstg-scanning-files">
        <span class="wpstg-tab-triangle">&#9658;</span>
        <?php echo __("Select Files", "wp-staging") ?>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-files">
        <h4>
            <?php echo __("Select plugins, themes & uploads folder to push to production website.", "wp-staging") ?>
        </h4>

        <p>
            <?php echo __("<strong style='color:red;'>Note:</strong> Your folders selection is stored automatically<br/>and will be used as default selection for the next push.", "wp-staging"); ?>
        </p>
        <?php echo $scan->directoryListing() ?>

        <h4 style="margin:10px 0 10px 0">
            <?php echo __("Extra Directories to Copy", "wp-staging") ?>
        </h4>

        <textarea id="wpstg_extraDirectories" name="wpstg_extraDirectories" style="width:100%;height:100px;"></textarea>
        <p>
            <span>
                <?php
                echo __(
                    "Enter one directory path per line.<br>" .
                    "Directory must start with absolute path: " . $options->root . $options->cloneDirectoryName, "wp-staging"
                )
                ?>
            </span>
        </p>

        <p>
            <span>
                <?php
                if (isset($options->clone)) {
                    echo __("Plugin files will be pushed to: ", "wp-staging") . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins';
                    echo '<br>';
                    echo __("Theme files will be pushed to: ", "wp-staging") . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
                    echo '<br>';
                    echo __("Media files will be pushed to: ", "wp-staging") . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads';
                }
                ?>
            </span>
        </p>
    </div>
    <p><label>
        <input type="checkbox" id="wpstg-remove-uninstalled-plugins-themes" name="wpstg-remove-uninstalled-plugins-themes">
        <?php echo __("Uninstall all plugins/themes on production site that are not installed on staging site.", "wp-staging"); ?>
    </label></p>
    <p><label> <?php echo ($options->uploadsSymlinked ? "<b>" . __("Note: This option is disabled as uploads dir was symlinked", "wp-staging") . "</b><br/>": '') ?>
        <input type="checkbox" id="wpstg-delete-upload-before-pushing" name="wpstg-delete-upload-before-pushing" <?php echo ($options->uploadsSymlinked ? 'disabled' : '') ?>>
        <?php echo __("Delete wp-content/uploads folder on production site including all images before starting copy process.", "wp-staging"); ?>
    </label></p>
    <p id="wpstg-backup-upload-container" style="display: none;"><label>
        <input type="checkbox" id="wpstg-backup-upload-before-pushing" name="wpstg-backup-upload-before-pushing" <?php echo ($options->uploadsSymlinked ? 'disabled' : '') ?>>
        <?php echo __("Create a backup of folder wp-content/uploads before deleting it. Helpful in case the push process fails. Make sure you have enough space for the uploads folder backup on your hosting otherwise the process will fail. Backup will be written to wp-content/uploads.wpstg_backup", "wp-staging"); ?>
    </label></p>
    <p><label>
            <input type="checkbox" id="wpstg-create-snapshot-before-pushing" name="wpstg-create-snapshot-before-pushing"
                   checked>
            <?php echo __("Create database backup (snapshot)", "wp-staging"); ?>
        </label>
    </p>
</div>

<div class="wpstg-progress-bar-wrapper" style="display: none;">
    <h2 id="wpstg-processing-header"><?php echo __("Processing, please wait...", "wp-staging") ?></h2>
    <div class="wpstg-progress-bar">
        <div class="wpstg-progress" id="wpstg-progress-backup"></div>
        <div class="wpstg-progress wpstg-pro" id="wpstg-progress-files"></div>
        <div class="wpstg-progress" id="wpstg-progress-data"></div>
        <div class="wpstg-progress" id="wpstg-progress-finishing"></div>
    </div>
    <div class="wpstg-clear-both">
        <div id="wpstg-processing-status"></div>
        <div id="wpstg-processing-timer"></div>
    </div>
    <div class="wpstg-clear-both"></div>
</div>

<div id="wpstg-error-wrapper">
    <div id="wpstg-error-details"></div>
</div>

<div class="wpstg-log-details" style="display: none;"></div>
<p></p>
<button type="button" class="wpstg-prev-step-link wpstg-link-btn wpstg-blue-primary">
    <?php _e("Back", "wp-staging") ?>
</button>

<button type="button" id="wpstg-push-changes" class="wpstg-next-step-link-pro wpstg-link-btn wpstg-blue-primary"
        data-action="wpstg_push_changes" data-clone="<?php echo $options->current; ?>">
    <?php
    echo __('Push Staging Site to Live Site', 'wp-staging');
    ?>
</button>
<p></p>
<?php
//$adminUrl = admin_url() . 'options-permalink.php';
//echo sprintf(__( "<strong>Note:</strong> If you push the database table '_users' you may have to login again. <br/>"
//. "After migration, go to <a href='%s' target='_new'>wp-admin > settings > permalinks</a> and save permalink settings to prevent any page not found errors 404.", "wp-staging" ), admin_url() . 'options-permalink.php');
?>