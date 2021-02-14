<input type="hidden" id="wpstg-edit-clone-data-clone-id" name="wpstg-clone-id" value="<?php
echo $_POST["clone"] ?>">
<div class="wpstg-form-horizontal">
    <div>
        <h3><?php _e('Update Clone Data', 'wp-staging');?></h3>
        <?php echo sprintf(__('Update the values below only if you moved your staging site to another server and WP STAGING lost connection to the clone site. Don\'t update these values if you are unsure. This can break the pushing capability. <a href="%s" target="_blank">Read More</a>.', 'wp-staging'), 'https://wp-staging.com/docs/reconnect-staging-site-to-production-website/'); ?>
    </div>
    &nbsp;
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-directory-name-label" for="wpstg-edit-clone-data-directory-name">
            <?php _e("Site Name", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-directory-name" name="wpstg-directory-name" value="<?php
        echo $clone['directoryName'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-path-label" for="wpstg-edit-clone-data-path">
            <?php
            _e("Target Directory", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-path" name="wpstg-path" value="<?php
        echo $clone['path'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-url-label" for="wpstg-edit-clone-data-url">
            <?php _e("Target Hostname", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-url" name="wpstg-url" value="<?php
        echo $clone['url'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-prefix-label" for="wpstg-edit-clone-data-prefix">
            <?php _e("Database Table Prefix", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-prefix" name="wpstg-prefix" value="<?php
        echo $clone['prefix'] ?>">
    </div>

    <div class="wpstg-form-row">
        <h3><?php _e('Database Access Data','wp-staging'); ?></h3>
        <?php _e("Don't modify values below if the staging site was not cloned into a separate database", "wp-staging"); ?>
    </div>
    &nbsp;
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-database-user-label" for="wpstg-edit-clone-data-database-user">
            <?php _e("Database User", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-database-user" name="wpstg-database-user" value="<?php
        echo $clone['databaseUser'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-database-password-label" for="wpstg-edit-clone-data-database-password">
            <?php _e("Database Password", "wp-staging"); ?>
        </label>
        <input type="password" id="wpstg-edit-clone-data-database-password" name="wpstg-database-password" value="<?php
        echo $clone['databasePassword'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-database-database-label" for="wpstg-edit-clone-data-database-database">
            <?php _e("Database Name", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-database-database" name="wpstg-database-database" value="<?php
        echo $clone['databaseDatabase'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-database-server-label" for="wpstg-edit-clone-data-database-server">
            <?php _e("Database Hostname", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-database-server" name="wpstg-database-server" value="<?php
        echo $clone['databaseServer'] ?>">
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-edit-clone-data-database-prefix-label" for="wpstg-edit-clone-data-database-prefix">
            <?php _e("Database Table Prefix", "wp-staging"); ?>
        </label>
        <input type="text" id="wpstg-edit-clone-data-database-prefix" name="wpstg-database-prefix" value="<?php
        echo $clone['databasePrefix'] ?>">
    </div>
</div>
<p></p>
<button type="button" class="wpstg-prev-step-link wpstg-link-btn wpstg-blue-primary">
    <?php
    _e("Back", "wp-staging") ?>
</button>
<button type="button" id="wpstg-save-clone-data" class="wpstg-link-btn wpstg-blue-primary">
    <?php _e('Save Clone Data', 'wp-staging'); ?>
</button>
<p></p>
