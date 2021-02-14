<?php
/**
 *	Infinite scroll pagination button
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$extra_classes
 *	@var		$show_more_text
 *	@var		$all_items_shown_text
 *	@var		$loading_style
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Classes
$classes = array( 'pagination', 'pagination--infinite-scroll' );

if ( ! empty( $extra_classes ) ) {
	$classes = array_merge( $classes, $extra_classes );
}

// Show more button classes
$show_more_classes = array( 'pagination--infinite-scroll-show-more', sprintf( 'pagination--infinite-scroll-loading-style-%s', $loading_style ) );

?>
<nav class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="navigation">
	
	<a href="#" class="<?php echo esc_attr( implode( ' ', $show_more_classes ) ); ?>" data-endless-pagination-id="<?php echo esc_attr( $id ); ?>">
		
		<span class="show-more-text"><?php echo $show_more_text; ?></span>
		
		<span class="all-items-shown"><?php echo $all_items_shown_text; ?></span>
		
		<span class="loading-spinner">
		
		<?php
			// Loader Type
			switch ( $loading_style ) :
			
				// Pulsating loader
				case 'pulsating' :
					?>
					<i class="loading-spinner-1"></i>
					<?php
					break;
					
				// Spinner loader
				default:
					?>
					<i class="fa fa-circle-o-notch fa-spin"></i>
					<?php
						
			endswitch;
		?>
			
		</span>
	</a>
	
</nav>