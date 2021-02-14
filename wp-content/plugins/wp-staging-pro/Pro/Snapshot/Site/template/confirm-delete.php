<?php
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Entity\Snapshot;

/** @var TemplateEngine $this */
/** @var Snapshot $snapshot */
?>
<div class="wpstg-notice-alert wpstg-failed">
    <h4 style="margin:0;">
        <?php _e('This backup will be deleted.', 'wp-staging' ) ?>
    </h4>
    <?php _e('Are you sure that you want to delete the site backup? This action can not be undone!', 'wp-staging') ?>
</div>

<div class="wpstg-box">
  <div class="wpstg-db-table">
    <label><?php _e('File Size', 'wp-staging')?></label>
    <span class="wpstg-size-info"><?php echo $snapshot->getFileSize() ?></span>
  </div>
</div>

<a href="#" class="wpstg-link-btn button-primary" id="wpstg-cancel-snapshot-delete">
    <?php _e('Cancel', 'wp-staging')?>
</a>

<a href="#" class="wpstg-link-btn button-primary" id="wpstg-delete-snapshot" data-id="<?php echo $snapshot->getId()?>">
    <?php _e('Delete', 'wp-staging')?>
</a>
