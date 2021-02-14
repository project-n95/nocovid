<?php
/**
 * This file is currently being called only for the Pro version:
 * src/Backend/views/clone/ajax/scan.php:113
 *
 * @file src/Backend/views/clone/ajax/custom-directory.php For the Free counterpart.
 */
if (empty($options->current) || $options->current === null) {
    ?>
    <hr>
    <p>
        <strong class="wpstg-fs-14"> <?php _e('Copy Staging Site to Custom Directory', 'wp-staging'); ?></strong>
        <br>
        <?php _e('Path must be writeable by PHP and an absolute path like <code>/www/public_html/dev</code>', 'wp-staging'); ?>
        <br/>
    </p>
    <?php
    /**
     * Used for overwriting the default target directory and target hostname via hook
     */
    $directory = apply_filters('wpstg_cloning_target_dir', \WPStaging\Core\WPStaging::getWPpath());
    $customDir = apply_filters('wpstg_cloning_target_dir', '');

    if (is_multisite() && !SUBDOMAIN_INSTALL) {
        $hostname = network_site_url();
    } else {
        $hostname = get_site_url();
    }
    $hostname = apply_filters('wpstg_cloning_target_hostname', $hostname);
    $customHostname = apply_filters('wpstg_cloning_target_hostname', '');

    ?>
    <div id="wpstg-clone-directory">
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Target Directory: ', 'wp-staging') ?> </label>
            <input type="text" name="wpstg_clone_dir" id="wpstg_clone_dir" value="<?php echo $customDir; ?>" title="wpstg_clone_dir" placeholder="<?php echo $directory; ?>" autocapitalize="off">
            <span class="wpstg-code-segment">
          <code>
            <a id="wpstg-use-target-dir" data-base-path="<?php echo $directory ?>" data-path="<?php echo $directory ?>" class="wpstg-pointer">
              <?php _e('Set Default: ', 'wp-staging') ?>
            </a>
            <span class="wpstg-use-target-dir--value">
              <?php echo $directory; ?>
            </span>
          </code>
        </span>
        </div>
        <p>
            <strong class="wpstg-fs-14"> <?php _e('Specify Target Hostname', 'wp-staging'); ?></strong>
            <br/>
            <?php _e('Set the hostname of the target site, for instance https://example.com or https://example.com/staging', 'wp-staging'); ?>
            <br/>
            <?php _e('Make sure the hostname points to the target directory from above.', 'wp-staging'); ?>
        </p>
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Target Hostname: ') ?> </label>
            <input type="text" name="wpstg_clone_hostname" id="wpstg_clone_hostname" value="<?php echo $customHostname; ?>" title="wpstg_clone_hostname" placeholder="<?php echo $hostname; ?>" autocapitalize="off">
            <span class="wpstg-code-segment">
              <code>
                <a id="wpstg-use-target-hostname" data-base-uri="<?php echo $hostname ?>" data-uri="<?php echo $hostname ?>" class="wpstg-pointer">
                  <?php _e('Set Default: ', 'wp-staging') ?>
                </a>
                <span class="wpstg-use-target-hostname--value">
                  <?php echo get_site_url(); ?>
                </span>
              </code>
          </span>
      </div>
    </div>
    <hr/>
    <p>
        <strong class="wpstg-fs-14"><?php _e('Symlink Uploads Folder', 'wp-staging'); ?></strong>
        <br/>
        <br/>
        <?php _e('Activate to symlink the folder <code>wp-content/uploads</code> to the production site. All images on the production site\'s uploads folder will be linked to the staging site uploads folder. This will speed up the cloning and pushing process tremendously as no images and other data is copied between both sites.', 'wp-staging'); ?>
        <br/>
        <br/>
        <?php _e('<strong>This feature only works if the staging site is on the same hosting as the production site.</strong>', 'wp-staging'); ?>
    </p>
    <div class="wpstg-form-group">
        <label class="wpstg-checkbox" for="wpstg_symlink_upload">
            <?php _e('Symlink Uploads Folder:', 'wp-staging'); ?>
            <input type="checkbox" name="wpstg_symlink_upload" id="wpstg_symlink_upload" value="true" title="wpstg_symlink_upload">
        </label>
    </div>
    <?php
} else {

    $cloneDir = isset($options->existingClones[$options->current]['cloneDir']) ? $options->existingClones[$options->current]['cloneDir'] : '';
    $hostname = isset($options->existingClones[$options->current]['url']) ? $options->existingClones[$options->current]['url'] : '';
    $directory = isset($options->existingClones[$options->current]['path']) ? $options->existingClones[$options->current]['path'] : '';
    $uploadSymlinked = isset($options->existingClones[$options->current]['uploadsSymlinked']) ? (bool)$options->existingClones[$options->current]['uploadsSymlinked'] : false;
    ?>
    <hr>
    <p>
        <strong class="wpstg-fs-14"> <?php _e('Copy Staging Site to Custom Directory', 'wp-staging'); ?></strong>
    </p>
    <div class="wpstg-mt-16" id="wpstg-clone-directory">
        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Target Directory: ', 'wp-staging') ?> </label>
            <input disabled="disabled" type="text" name="wpstg_clone_dir" id="wpstg_clone_dir" value="<?php echo $directory; ?>" title="wpstg_clone_dir" placeholder="<?php echo \WPStaging\Core\WPStaging::getWPpath(); ?>" autocapitalize="off">
        </div>

        <p class="wpstg-w-100"><strong class="wpstg-fs-14"> <?php _e('Specify Target Hostname', 'wp-staging'); ?></strong>
            <br/>
            <?php _e('Make sure the hostname points to the target directory from above.', 'wp-staging'); ?>
        </p>

        <div class="wpstg-form-group wpstg-text-field">
            <label><?php _e('Target Hostname: ') ?> </label>
            <input disabled="disabled" type="text" name="wpstg_clone_hostname" id="wpstg_clone_hostname" value="<?php echo $hostname; ?>" title="wpstg_clone_hostname" placeholder="<?php echo $directory; ?>" autocapitalize="off">
            <span class="wpstg-code-segment">
              <code>
                <a id="wpstg-use-target-hostname" data-base-uri="<?php echo $hostname ?>" data-uri="<?php echo $hostname ?>" class="wpstg-pointer">
                  <?php _e('Reset: ', 'wp-staging') ?>
                </a>
                <span class="wpstg-use-target-hostname--value">
                  <?php echo $hostname; ?>
                </span>
              </code>
          </span>
      </div>
    </div>
    <p>
        <strong class="wpstg-fs-14"> <?php _e('Symlink Upload Folder', 'wp-staging'); ?></strong>
        <br/>
        <?php _e('(Create a new staging site if you want to change this setting.)', 'wp-staging'); ?>
    </p>
    <div class="wpstg-form-group">
        <label class="wpstg-checkbox" for="wpstg_symlink_upload">
            <?php _e('Symlink Upload Folder:', 'wp-staging'); ?>
            <input disabled="disabled" type="checkbox" name="wpstg_symlink_upload" id="wpstg_symlink_upload" value="true" title="wpstg_symlink_upload" <?php echo $uploadSymlinked === true ? 'checked' : '' ?>>
        </label>
    </div>

<?php } ?>

