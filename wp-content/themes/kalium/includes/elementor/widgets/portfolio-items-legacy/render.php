<?php
/**
 * Kalium WordPress Theme
 *
 * Render widget output.
 *
 * @var Widget_Base $this
 * @var array       $settings
 * @var string      $title
 * @var string      $description
 * @var string      $category_filter
 * @var string      $default_category_filter
 * @var int         $columns
 * @var string      $item_layout_type
 * @var string      $items_reveal_effect
 * @var bool        $dynamic_image_height
 * @var array       $container_classes
 * @var array       $portfolio_container_classes
 * @var array       $portfolio_args
 * @var string      $pagination_type
 * @var string      $pagination_static_link_button_text
 * @var array       $pagination_static_link_button_link
 * @var string      $pagination_infinite_scroll_auto_reveal
 * @var number      $pagination_infinite_scroll_per_page
 * @var string      $pagination_infinite_scroll_button_text
 * @var string      $pagination_infinite_scroll_reached_end_text
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<div <?php kalium_class_attr( $container_classes ); ?>>

	<?php
	/**
	 * Portfolio heading.
	 */
	include locate_template( 'tpls/portfolio-listing-title.php' );
	?>

    <div class="row">

		<?php
		/**
		 * Hook: kalium_portfolio_items_before.
		 *
		 * @param array $portfolio_query
		 */
		do_action( 'kalium_portfolio_items_before', $portfolio_query );
		?>

        <div id="<?php echo $portfolio_args['id']; ?>" <?php kalium_class_attr( apply_filters( 'kalium_portfolio_container_classes', $portfolio_container_classes ) ); ?>>
			<?php kalium_portfolio_loop_items_show( $portfolio_args ); ?>
        </div>

		<?php
		/**
		 * Hook: kalium_portfolio_items_after.
		 */
		do_action( 'kalium_portfolio_items_after' );

		// Generate Portfolio Instance Object
		kalium_portfolio_generate_portfolio_instance_object( $portfolio_args );


		// Static Pagination type
		if ( 'static-link' === $pagination_type ) :
			$target = '_self';
			$rel = [];

			if ( $pagination_static_link_button_link['is_external'] ) {
				$target = '_blank';
				$rel[]  = 'noopener';
			}

			if ( 'on' === $pagination_static_link_button_link['nofollow'] ) {
				$rel[] = 'nofollow';
			}

			$rel = implode( ' ', $rel );

			?>
            <div class="more-link <?php echo isset( $show_effect ) && $show_effect ? $show_effect : ''; ?>">
                <div class="show-more">
                    <div class="reveal-button">
                        <a href="<?php echo esc_url( $pagination_static_link_button_link['url'] ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $rel ); ?>" class="btn btn-white">
							<?php echo esc_html( $pagination_static_link_button_text ); ?>
                        </a>
                    </div>
                </div>
            </div>
		<?php

		// Infinite scroll pagination
        elseif ( 'infinite-scroll' === $pagination_type ) :
			kalium_portfolio_endless_pagination( $portfolio_args );
		endif;
		?>

    </div>

    <script>
		jQuery( document ).ready( function ( $ ) {
			$( '.portfolio-holder' ).isotope( {
				itemSelector: '.portfolio-item',
			} );
		} );
    </script>

</div>
