<?php
/**
 *	Products Carousel Shortcode for Visual Composer
 *
 *	Laborator.co
 *	www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_products_carousel/carousel.svg' );

// Shortcode Options
vc_map( array(
	"name"		=> "Products Carousel",
	"description" => 'Display shop products with Touch Carousel.',
	"base"		=> "lab_products_carousel",
	"class"		=> "vc_lab_products_carousel",
	'icon'      => $lab_vc_element_icon,
	"controls"	=> "full",
	"category"  => array( 'Laborator', 'WooCommerce' ),
	"params"	=> array(

		array(
			"type" => "loop",
			"heading" => "Products Query",
			"param_name" => "products_query",
			'settings' => array(
				'size' => array('hidden' => false, 'value' => 12),
				'order_by' => array('value' => 'date'),
				'post_type' => array('value' => 'product', 'hidden' => false)
			),
			"description" => "Create WordPress loop, to populate products from your site."
		),

		array(
			"type" => "dropdown",
			"heading" => "Filter Products by Type",
			"param_name" => "product_types_to_show",
			"value" => array(
				"Show all types of products from the above query"  => '',
				"Show only featured products from the above query."  => 'only_featured',
				"Show only products on sale from the above query."  => 'only_on_sale',
			),
			"description" => "Filter products from the above query to show featured or on sale products.",
		),

		array(
			"type" => "dropdown",
			"heading" => "Columns",
			"param_name" => "columns",
			"std" => 4,
			"value" => array(
				"1 column"   => 1,
				"2 columns"  => 2,
				"3 columns"  => 3,
				"4 columns"  => 4,
				"5 columns"  => 5,
				"6 columns"  => 6,
			),
			"description" => "Select number of columns to show products."
		),

		array(
			"type" => "textfield",
			"heading" => "Auto Rotate",
			"param_name" => "auto_rotate",
			"value" => "5",
			"description" => "You can set automatic rotation of carousel, unit is seconds. Enter 0 to disable."
		),
/*

		array(
			"type" => "dropdown",
			"heading" => "Products per row on mobile devices",
			"param_name" => "columns_mobile",
			"std" => 4,
			"value" => array(
				"1 product per row"  => 1,
				"2 products per row"  => 2,
			),
			"description" => "Set how many products to show on mobile screen size."
		),
*/

		array(
			"type" => "textfield",
			"heading" => "Extra class name",
			"param_name" => "el_class",
			"value" => "",
			"description" => "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file."
		),

		array(
			"type" => "css_editor",
			"heading" => 'Css',
			"param_name" => "css",
			"group" => 'Design options'
		)
	)
) );

class WPBakeryShortCode_lab_products_carousel extends  WPBakeryShortCode {
	/**
	 * Ids to exclude in products query
	 */
	private $exclude_ids = array();
	
	/**
	 * Tax query
	 */
	private $tax_query = array();
	
	/**
	 * Shortcode content
	 */
	public function content( $atts, $content = null ) {
		global $woocommerce_loop;
		
		if ( ! kalium()->is->woocommerce_active() ) {
			return '';
		}
		
		kalium_vc_loop_param_set_default_value( $atts['products_query'], 'size', '12' );
		
		$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
		
		extract( shortcode_atts( array(
			'products_query'         => '',
			'product_types_to_show'  => '',
			'columns'                => '',
			'auto_rotate'            => '',
			'el_class'               => '',
			'css'                    => '',
		), $atts ) );
		

		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_content_element woocommerce woocommerce-products-carousel lab-vc-products-carousel products-hidden ' . $el_class . vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

		if ( $columns == 1 ) {
			$css_class .= ' single-column';
		}
		
		// Generate query using WC_Shortcode_Products class
		$query_args = kalium_vc_query_builder( $products_query );
		
		$atts = array(
			'columns' => $columns
		);
		
		$type = 'products';
		
		// Items per page
		if ( ! empty( $query_args['posts_per_page'] ) ) {
			$atts['limit'] = $query_args['posts_per_page'];
		}
		
		// Order column
		if ( ! empty( $query_args['orderby'] ) ) {
			$atts['orderby'] = $query_args['orderby'];
		}
		
		// Order direction
		if ( ! empty( $query_args['order'] ) ) {
			$atts['order'] = $query_args['order'];
		}
		
		// Tax Query
		if ( ! empty( $query_args['tax_query'] ) ) {
			$this->tax_query = $categories = array();
			
			foreach ( $query_args['tax_query'] as $i => $tax ) {
				
				if ( is_numeric( $i ) && ! empty( $tax['taxonomy'] ) ) {
					// Product Categories
					if ( 'product_cat' == $tax['taxonomy'] ) {
						if ( 'NOT IN' == strtoupper( $tax['operator'] ) ) {
							$this->tax_query[] = $tax;
						} else {
							foreach ( $tax['terms'] as $term_id ) {
								if ( $term = get_term( $term_id, 'product_cat' ) ) {
									$categories[] = $term->slug;
								}
							}
						}
					} 
					// Other terms
					else {
						$this->tax_query[] = $tax;
					}
				}
			}
			
			// Categories
			$atts['category'] = implode( ',', $categories );
			
			// Add tax query to products query
			
			if ( count( $this->tax_query ) ) {
				add_filter( 'woocommerce_shortcode_products_query', array( $this, 'addTaxQuery' ), 100, 3 );
			}
		}
		
		// Include post ids
		if ( ! empty( $query_args['post__in'] ) ) {
			$atts['ids'] = implode( ',', $query_args['post__in'] );
		}
		
		// Exclude post ids
		if ( ! empty( $query_args['post__not_in'] ) ) {
			$this->exclude_ids = $query_args['post__not_in'];
			add_filter( 'woocommerce_shortcode_products_query', array( $this, 'excludeIds' ), 100, 3 );
		}
		
		// Featured items only
		if ( 'only_featured' == $product_types_to_show ) {
			$atts['visibility'] = 'featured';
			$type = 'featured_products';
		}
		
		// On sale products
		if ( 'only_on_sale' == $product_types_to_show ) {
			$type = 'sale_products';
		}
		
		// Get products
		$shortcode = new WC_Shortcode_Products( $atts, $type );
		

		// DOMElement ID
		$rand_id = "el_" . time() . mt_rand( 10000,99999 );
		$columns = absint( $columns );

		// Enqueue slick carousel
		kalium_enqueue_slick_slider_library();

		ob_start();

		?>
		<div class="<?php echo esc_attr( "$rand_id $css_class" ); ?>">

			<div class="shop-loading-products">
				<?php _e( 'Loading products...', 'kalium' ); ?>
			</div>

			<?php			
				// Show products
				echo $shortcode->get_content();
			?>
			
		</div>
		
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				
				var $productsCarouselContainer = $( '.<?php echo $rand_id; ?>' ),
					$productsCarousel = $productsCarouselContainer.find( '.products' );

				$productsCarouselContainer.removeClass( 'products-hidden' );

				$productsCarousel.slick( {
					slide : '.product',
					infinite : true, // Experimental
					slidesToShow : <?php echo apply_filters( 'kalium_woocommerce_products_carousel_slides_to_show', $columns, 'desktop' ); ?>,
					slidesToScroll : 1,					
					prevArrow : '<span class="nextprev-arrow ss-prev"><i class="flaticon-arrow427"></i></span>',
					nextArrow : '<span class="nextprev-arrow ss-next"><i class="flaticon-arrow413"></i></span>',
					adaptiveHeight : true,				
					<?php if ( $auto_rotate > 0 ) : ?>
					autoplay : true,
					autoplaySpeed : <?php echo $auto_rotate * 1000; ?>,
					<?php endif; ?>
					responsive : [
						{
							breakpoint : 1119,
							settings : {
								slidesToShow : <?php echo apply_filters( 'kalium_woocommerce_products_carousel_slides_to_show', min( $columns, 3 ), 'desktop' ); ?>
							}
						},
						{
							breakpoint : 768,
							settings : {
								slidesToShow : <?php echo apply_filters( 'kalium_woocommerce_products_carousel_slides_to_show', 2, 'tablet' ); ?>
							}
						},
						{
							breakpoint : 480,
							settings : {
								slidesToShow : <?php echo apply_filters( 'kalium_woocommerce_products_carousel_slides_to_show', kalium_woocommerce_products_per_row_on_mobile(), 'mobile' ); ?>
							}
						}
					]
				} );
			} );
		</script>
		<?php

		$output = ob_get_clean();

		return $output;
	}
	
	/**
	 * Exclude Ids from query
	 */
	public function excludeIds( $query, $atts, $type ) {
		
		if ( empty( $query['post__not_in'] ) ) {
			$query['post__not_in'] = array();
		}
		
		// Exclude ids
		$query['post__not_in'] = array_merge( $query['post__not_in'], $this->exclude_ids );
		
		// Remove filter after execution
		remove_filter( 'woocommerce_shortcode_products_query', array( $this, 'excludeIds' ), 100, 3 );

		return $query;
	}
	
	/**
	 * Add tax query
	 */
	public function addTaxQuery( $query, $atts, $type ) {
		$tax_query_default = array(
			'field' => 'term_id',
			'taxonomy' => '',
			'operator' => 'IN',
			'terms' => array()
		);
		
		if ( empty( $query['tax_query'] ) ) {
			$query['tax_query'] = array(
				'relation' => 'AND'
			);
		}
		
		foreach ( $this->tax_query as $tax_query ) {
			$query['tax_query'][] = array_merge( $tax_query_default, $tax_query );
		}
		
		// Remove filter after execution
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'addTaxQuery' ), 100, 3 );
		
		return $query;
	}
}
