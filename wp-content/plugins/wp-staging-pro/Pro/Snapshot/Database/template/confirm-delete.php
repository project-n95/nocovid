<?php
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Collection\Collection;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Entity\Snapshot;

/** @var TemplateEngine $this */
/** @var Snapshot $snapshot */
/** @var TableDto[]|Collection $tables */
?>
<div class="wpstg-notice-alert wpstg-failed">
    <h4 style="margin:0;">
        <?php _e('This backup will be deleted.', 'wp-staging' ) ?>
    </h4>
    <?php _e('The database tables below contain the backup data. Are you sure that you want to delete all these tables? This action can not be undone!', 'wp-staging') ?>
    <p></p>
    <strong><?php _e('Explanation:', 'wp-staging'); ?></strong>
    <br>
    <?php _e('- wpsm(int)_table_name: Backup tables that you\'ve created manually.', 'wp-staging') ?>
    <br>
    <?php _e('- wpsa(int)_table_name:  Backup tables that have been created automatically when you pushed a staging site to production. They can be used to revert a particular push.', 'wp-staging') ?>
</div>

<div class="wpstg-box">
  <?php foreach ($tables as $table):?>
    <div class="wpstg-db-table">
      <label><?php echo $table->getName()?></label>
      <span class="wpstg-size-info"><?php echo $table->getHumanReadableSize()?></span>
    </div>
  <?php endforeach ?>
</div>

<a href="#" class="wpstg-link-btn button-primary" id="wpstg-cancel-snapshot-delete">
    <?php _e('Cancel', 'wp-staging')?>
</a>

<a href="#" class="wpstg-link-btn button-primary" id="wpstg-delete-snapshot" data-id="<?php echo $snapshot->getId()?>">
    <?php _e('Delete', 'wp-staging')?>
</a>
