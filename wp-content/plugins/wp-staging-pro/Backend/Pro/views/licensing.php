<div class="wpstg_admin">
    <?php require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/header.php'); ?>

    <label for="wpstg_license_key" style='display:block;margin-bottom: 5px;margin-top:10px;'><?php _e('Enter License Key to activate WP STAGING Pro:','wp-staging'); ?></label>
      <form method="post" action="#">

      <input type="text" name="wpstg_license_key" style="width:260px;" value='<?php echo get_option('wpstg_license_key', ''); ?>'>
      <?php

      if (isset($license->error) && $license->error === 'expired'){
         $message =  '<span style="color:red;">' . __('Your license expired on ', 'wp-staging') . date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) )) . '</span>';
      } else if (isset($license->license) && $license->license === 'valid') {
         $message =  __('You\'ll get updates and support until ', 'wp-staging') . date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ));
         $message .= '<p><a href="'.admin_url().'admin.php?page=wpstg_clone" id="wpstg-new-clone" class="wpstg-next-step-link wpstg-link-btn button-primary">Go to Start</a>';
      } else {
         $message = '';
      }

      wp_nonce_field( 'wpstg_license_nonce', 'wpstg_license_nonce' );
      if( isset( $license->license ) && $license->license === 'valid' ) {
         echo '<input type="hidden" name="wpstg_deactivate_license" value="1" />';
         echo '<input type="submit" class="button" value="' . __( 'Deactivate License', 'wp-staging' ) . '">';
      } else {
         echo '<input type="hidden" name="wpstg_activate_license" value="1" />';
         echo '<input type="submit" class="button-primary" value="' . __( 'Activate License', 'wp-staging' ) . '">';
      }
      ?>
        </form>
        <?php echo '<div style="padding:3px;font-style:italic;">'.$message . '</div>'; ?>
    </div>
</div>