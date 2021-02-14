<?php
/**
 *	Normal pagination (1,2,3...)
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$pagination_args
 *	@var		$extra_classes
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
 
// Pagination defaults
$pagination_args = array_merge( array(
	'mid_size'  => 2,
	'end_size'  => 2,
	'prev_text' => sprintf( '%2$s %1$s', __( 'Previous', 'kalium' ), '<i class="flaticon-arrow427"></i>' ),
	'next_text' => sprintf( '%1$s %2$s', __( 'Next', 'kalium' ), '<i class="flaticon-arrow413"></i>' ),
), $pagination_args );

// Generate pagination
$pagination = paginate_links( apply_filters( 'kalium_pagination_args', $pagination_args ) );

// Classes
$classes = array( 'pagination', 'pagination--normal' );

if ( ! empty( $extra_classes ) ) {
	$classes = array_merge( $classes, $extra_classes );
}
?>
<nav class="<?php echo esc_attr( implode( ' ', $classes  ) ); ?>" role="navigation">
	
	<?php echo $pagination; ?>
	
</nav>