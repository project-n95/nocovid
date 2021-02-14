<?php
/**
 * This file is currently being called only for the Pro version:
 * src/Backend/views/clone/ajax/scan.php:113
 *
 * @file src/Backend/views/clone/ajax/external-database.php For the Free counterpart.
 */
if( empty( $options->current ) || $options->current === null ) {
    ?>

    <p><label><input type="checkbox" id="wpstg-ext-db" name="wpstg-ext-db" value="true">
            <strong style="font-size: 14px;"><?php _e( 'Copy Staging Site to Separate Database', 'wp-staging' ); ?></strong>
            <br><?php _e('Database must be created manually in advance!<br/><br/><strong>Important:</strong> If there are already tables with the same database prefix and name in the database the process will be aborted without any further asking!', 'wp-staging'); ?>
        </label></p>
    <div id="wpstg-external-db">
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Server: ', 'wp-staging'); ?> </label>
            <input type="text" name="wpstg_db_server" id="wpstg_db_server" value="" title="wpstg_db_server" placeholder="localhost" autocapitalize="off" readonly>
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('User: ', 'wp-staging'); ?></label>
            <input type="text" name="wpstg_db_username" id="wpstg_db_username" value="" autocapitalize="off" class="" readonly>
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Password: ', 'wp-staging'); ?></label>
            <input type="password" name="wpstg_db_password" id="wpstg_db_password" class="" readonly>
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Database: ', 'wp-staging'); ?></label>
            <input type="text" name="wpstg_db_database" id="wpstg_db_database" value="" autocapitalize="off" readonly>
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Database Prefix: ', 'wp-staging'); ?></label>
            <input type="text" name="wpstg_db_prefix" id="wpstg_db_prefix" value="" placeholder="<?php echo $db->prefix; ?>" autocapitalize="off" readonly>
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label>&nbsp;</label>
            <a href="#" id="wpstg-db-connect"><?php _e("Test Database Connection", "wp-staging"); ?></a>
        </div>
    </div>

    <?php
} else {

    $database = isset( $options->existingClones[$options->current]['databaseDatabase'] ) ? $options->existingClones[$options->current]['databaseDatabase'] : '';
    $user     = isset( $options->existingClones[$options->current]['databaseUser'] ) ? $options->existingClones[$options->current]['databaseUser'] : '';
    $password = isset( $options->existingClones[$options->current]['databasePassword'] ) ? $options->existingClones[$options->current]['databasePassword'] : '';
    $prefix   = isset( $options->existingClones[$options->current]['databasePrefix'] ) ? $options->existingClones[$options->current]['databasePrefix'] : '';
    $server   = isset( $options->existingClones[$options->current]['databaseServer'] ) ? $options->existingClones[$options->current]['databaseServer'] : '';
    ?>
    <div id="wpstg-external-db">
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Server: ', 'wp-staging'); ?> </label>
            <input disabled="disabled" readonly type="text" name="wpstg_db_server" id="wpstg_db_server" value="<?php echo $server; ?>" title="wpstg_db_server" placeholder="localhost" autocapitalize="off">
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('User: ', 'wp-staging'); ?></label>
            <input disabled="disabled" readonly type="text" name="wpstg_db_username" id="wpstg_db_username" value="<?php echo $user; ?>" autocapitalize="off" class="">
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Password: ', 'wp-staging'); ?></label>
            <input disabled="disabled" readonly type="password" name="wpstg_db_password" id="wpstg_db_password" class="" value="*********">
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Database: ', 'wp-staging'); ?></label>
            <input disabled="disabled" readonly type="text" name="wpstg_db_database" id="wpstg_db_database" value="<?php echo $database; ?>" autocapitalize="off">
        </div>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Database Prefix: ', 'wp-staging'); ?></label>
            <input disabled="disabled" readonly type="text" name="wpstg_db_prefix" id="wpstg_db_prefix" value="<?php echo $prefix; ?>" placeholder="<?php echo $db->prefix; ?>" autocapitalize="off">
        </div>
    </div>

<?php } ?>