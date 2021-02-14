<?php
/**
 * Kalium WordPress Theme
 *
 * Coming soon mode.
 *
 * @var int    $logo_id
 * @var int    $logo_max_width
 * @var string $page_description
 * @var bool   $set_countdown
 * @var string $countdown_date
 * @var bool   $social_networks
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Theme header.
 */
get_header();

?>
    <div class="container">

        <div class="page-container">
            <div class="coming-soon-container">
                <div class="message-container wow fadeIn">
					<?php kalium_logo_element( $logo_id, $logo_max_width ); ?>
					<?php echo kalium_format_content( $page_description ); ?>
                </div>

				<?php if ( $set_countdown ) : ?>
                    <div class="countdown-holder">
                        <div class="col-sm-12">
                            <ul class="countdown">
                                <div class="row">
                                    <div data-wow-duration="1.0s" data-wow-delay="0.1"
                                         class="col-sm-offset-2 col-sm-2 col-xs-3 wow fadeIn">
                                        <span class="days">&nbsp;</span>
                                        <p class="timeRefDays" data-text="<?php esc_html_e( 'Days', 'kalium' ); ?>"
                                           data-text-singular="<?php esc_html_e( 'Day', 'kalium' ); ?>">&nbsp;</p>
                                    </div>
                                    <div data-wow-duration="1.5s" data-wow-delay="0.2"
                                         class="col-sm-2 col-xs-3 wow fadeIn">
                                        <span class="hours">&nbsp;</span>
                                        <p class="timeRefHours" data-text="<?php esc_html_e( 'Hours', 'kalium' ); ?>"
                                           data-text-singular="<?php esc_html_e( 'Hour', 'kalium' ); ?>">&nbsp;</p>
                                    </div>
                                    <div data-wow-duration="2.0s" data-wow-delay="0.35"
                                         class="col-sm-2 col-xs-3 wow fadeIn">
                                        <span class="minutes">&nbsp;</span>
                                        <p class="timeRefMinutes" data-text="<?php esc_html_e( 'Minutes', 'kalium' ); ?>"
                                           data-text-singular="<?php esc_html_e( 'Minute', 'kalium' ); ?>">&nbsp;</p>
                                    </div>
                                    <div data-wow-duration="2.5s" data-wow-delay="0.6"
                                         class="col-sm-2 col-xs-3 wow fadeIn">
                                        <span class="seconds">&nbsp;</span>
                                        <p class="timeRefSeconds" data-text="<?php esc_html_e( 'Seconds', 'kalium' ); ?>"
                                           data-text-singular="<?php esc_html_e( 'Second', 'kalium' ); ?>">&nbsp;</p>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <script type="text/javascript">
						jQuery( document ).ready( function ( $ ) {
							$( '.countdown' ).countdown( {
								date: '<?php echo esc_attr( $countdown_date ); ?>',
								format: 'on',
							} );
						} );
                    </script>
				<?php endif; ?>

				<?php if ( $social_networks ) : ?>
                    <div class="social-networks-env wow fadeIn" data-wow-delay="0.2">
						<?php echo do_shortcode( '[lab_social_networks rounded]' ); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>

    </div>
<?php

/**
 * Theme header.
 */
get_footer();
