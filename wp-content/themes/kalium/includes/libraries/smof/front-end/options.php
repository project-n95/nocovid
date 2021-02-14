<?php
global $options_machine;
$saved_text = 'Settings Saved';
?>
<div class="wrap" id="of_container">

    <div id="of-popup-save" class="of-save-popup">
        <div class="of-save-save"><i class="dashicons dashicons-thumbs-up"></i> Options Updated</div>
    </div>

    <div id="of-popup-reset" class="of-save-popup">
        <div class="of-save-reset"><i class="dashicons dashicons-update"></i> Options Reset</div>
    </div>

    <div id="of-popup-fail" class="of-save-popup">
        <div class="of-save-fail"><i class="dashicons dashicons-no"></i> Error!</div>
    </div>

    <span style="display: none;" id="hooks"><?php echo json_encode( of_get_header_classes_array() ); ?></span>
    <input type="hidden" id="reset" value="<?php if ( isset( $_REQUEST['reset'] ) ) {
		echo $_REQUEST['reset'];
	} ?>"/>
    <input type="hidden" id="security" name="security" value="<?php echo wp_create_nonce( 'of_ajax_nonce' ); ?>"/>

    <form id="of_form" method="post" action="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ) ?>" enctype="multipart/form-data">

        <div id="header">

            <div class="logo">
                <h2>
					<?php echo THEMENAME; ?>
                    <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'whats-new' ) . '#changelog' ); ?>" class="theme_version"><?php echo kalium()->get_version(); ?></a>
                </h2>

				<?php if ( kalium_is_holiday_season() ) : ?>
                    <div class="holidays-pine"></div>
				<?php endif; ?>
            </div>

            <div id="js-warning">Warning: This options panel will not work properly without javascript!</div>
            <a href="https://laborator.co" target="_blank" class="icon-option">
                <?php echo kalium_get_svg_file( 'assets/admin/images/laborator-logo.svg' ); ?>
            </a>
            <div class="clear"></div>

        </div>

        <div id="info_bar" class="hidden">

            <a>
                <div id="expand_options" class="expand">
                    <?php echo kalium_get_svg_file( 'assets/admin/images/theme-options/icon-expand.svg' ); ?>
                </div>
            </a>

            <button id="of_save" type="button" class="button-primary of-save-button">
				<span class="loading-spinner">
					<i class="dashicons dashicons-update spin"></i>
				</span>
                <em data-success="<?php echo $saved_text; ?>">Save All Changes</em>
            </button>

        </div><!--.info_bar-->

        <div id="main">

            <div id="of-nav">
                <ul>
					<?php echo $options_machine->Menu ?>
                </ul>
            </div>

            <div id="content">
				<?php echo $options_machine->Inputs /* Settings */ ?>
            </div>

            <div class="clear"></div>

            <a href="#of_save" class="of-save-button of-save-sticky">
                <span class="icons">
                    <i class="icon-save">
                        <?php echo kalium_get_svg_file( 'assets/admin/images/theme-options/icon-save.svg', 'save-options' ); ?>
                    </i>
                    <i class="icon-saving dashicons dashicons-update spin"></i>
                </span>
                <span class="save-text">Save All Changes</span>
            </a>

        </div>

        <div class="save_bar">

            <button id="of_save_2" type="button" class="button-primary of-save-button">
				<span class="loading-spinner">
					<i class="dashicons dashicons-update spin"></i>
				</span>
                <em data-success="<?php echo $saved_text; ?>">Save All Changes</em>
            </button>
            <button id="of_reset" type="button" class="button submit-button reset-button">Options Reset</button>

        </div><!--.save_bar-->

    </form>

    <div style="clear:both;"></div>

</div><!--wrap-->