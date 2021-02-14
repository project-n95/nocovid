<hr>
<p>
    <strong style="font-size: 14px;"> <?php _e( 'Mail Delivery Setting', 'wp-staging' ); ?></strong>
    <br/>
    <?php _e( 'Check to prevent staging site from sending emails.', 'wp-staging' ); ?>
</p>
<?php
if( empty( $options->current ) ) {
    /*
     * New staging site.
     * Disable emails is checked by default.
     */
    ?>
    <div class="wpstg-form-group">
        <label for="wpstg_disable_emails">
            <?php _e( 'Disable Emails:', 'wp-staging' ); ?> <input type="checkbox" name="wpstg_disable_emails" id="wpstg_disable_emails" checked>
        </label>
    </div>

    <?php
} else {
    /*
     * Existing staging site.
     * We read the site configuration. If none set, default to unchecked, since not having the setting
     * to disable the email in the database means it was not disabled.
     */
    $emailsDisabled  = isset( $options->existingClones[$options->current]['emailsDisabled'] ) ? (bool) $options->existingClones[$options->current]['emailsDisabled'] : false;
    ?>

    <div class="wpstg-form-group">
        <label>
            <?php _e( 'Disable Emails:', 'wp-staging' ); ?> <input type="checkbox" name="wpstg_disable_emails" id="wpstg_disable_emails" <?php echo $emailsDisabled === true ? 'checked' : '' ?>>
        </label>
    </div>

<?php } ?>
