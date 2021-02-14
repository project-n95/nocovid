<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// What to show
$portfolio_loop_subtitles = $portfolio_args['subtitles'];

// Hide option is selected
if ( $portfolio_loop_subtitles == 'hide' ) {
	return;
}

// Portfolio loop item categories before hooks
do_action( 'kalium_portfolio_loop_item_categories_before' );

// Show Subtitle
if ( 'subtitle' == $portfolio_loop_subtitles && $portfolio_item_subtitle ) {
	echo '<p class="sub-title">' . do_shortcode( $portfolio_item_subtitle ) . '</p>';	
}
// Categories
elseif ( in_array( $portfolio_loop_subtitles, array( 'categories', 'categories-parent' ) ) && ! empty( $portfolio_item_terms ) && ! is_wp_error( $portfolio_item_terms ) ) {
	$j = 0;
	
	echo '<p class="terms">';
	
	foreach ( $portfolio_item_terms as $term ) :
	
		// Parent Categories Check
		if ( $portfolio_loop_subtitles == 'categories-parent' && $term->parent != 0 ) {
			continue;
		}
	
		// Term Separator
		echo $j > 0 ? ', ' : '';
	
		?><a href="<?php echo esc_url( kalium_portfolio_get_category_link( $term ) ); ?>" data-term="<?php echo esc_attr( $term->slug ); ?>">
			<?php echo esc_html( $term->name ); ?>
		</a><?php
	
		$j++;
	
	endforeach;
	
	echo '</p>';
}

// Portfolio loop item categories after hooks
do_action( 'kalium_portfolio_loop_item_categories_after' );
