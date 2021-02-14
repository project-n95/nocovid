<?php

use WPStaging\Pro\Snapshot\Site\Service\ExportFileHeadersDto;

?>
<div id="wpstg-confirm-snapshot-restore-wrapper">
<div class="wpstg-confirm-snapshot-restore-header">
    <h3 style="margin:0;"><?php _e('This will restore your website! </br> Are you sure?', 'wp-staging'); ?></h3>
</div>
<div id="wpstg-confirm-snapshot-restore-data">
    <ul>
        <?php if ($info->isDatabaseIncluded()){ ?>
        <li style="list-style-type: square;"><?php /** @var ExportFileHeadersDto $info */  _e('Database will be replaced.', 'wp-staging'); ?></li>
        <?php }?>
        <li style="list-style-type: square;"><?php _e('Plugins will be replaced.', 'wp-staging') ?></li>
        <li style="list-style-type: square;"><?php _e('Media files and images will be merged. ', 'wp-staging') ?></li>
    </ul>
    <span style="font-weight:bold;"><?php _e('These folders will be restored:', 'wp-staging') ?></span>
    <div class="wpstg-db-table" style="display:none;">
        <strong><?php _e('WP Staging Version', 'wp-staging') ?></strong>
        <span class=""><?php echo $info->getVersion() ?></span>
    </div>
    <div class="wpstg-db-table" style="margin-top:5px;display: none;">
        <strong><?php _e('Total Directories', 'wp-staging') ?></strong>
        <span class=""><?php //echo $info->getTotalDirectories() ?></span>
    </div>
    <div class="wpstg-db-table" style="margin-top:5px;">
        <ul>
        <?php foreach ($info->getDirectories() as $directory): ?>
            <li style="list-style-type: square;"><span class=""><?php echo $directory ?></span></li>
        <?php endforeach ?>
        </ul>
    </div>
    <div class="wpstg-db-table" style="margin-top:5px;">
        <strong><?php _e('Total Files:', 'wp-staging') ?></strong>
        <span class=""><?php echo $info->getTotalFiles() ?></span>
    </div>
    <?php if (!empty($_POST['search'])): ?>
        <div class="wpstg-db-table" style="margin-top:20px;">
            <?php foreach ($_POST['search'] as $index => $search): ?>
                <span class=""><?php echo sprintf(__('Search: %s', 'wp-staging'), $search) ?></span> <br/>
                <span class=""><?php echo sprintf(__('Replace: %s', 'wp-staging'), $_POST['replace'][$index]) ?></span>
            <hr>
                <br/>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
</div>
