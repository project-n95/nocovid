<?php
/**
 * Kalium WordPress Theme
 *
 * WooCommerce template functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Archive wrapper before.
 */
if ( ! function_exists( 'kalium_woocommerce_archive_wrapper_start' ) ) {

	function kalium_woocommerce_archive_wrapper_start() {

		// Show on archive and product taxonomy page
		if ( ! ( is_shop() || is_product_taxonomy() ) ) {
			return;
		}

		$shop_sidebar = kalium_woocommerce_get_sidebar_position();

		$products_archive_classes = [
			'products-archive',
		];

		// Shop sidebar
		if ( in_array( $shop_sidebar, [ 'left', 'right' ] ) ) {
			$products_archive_classes[] = 'products-archive--has-sidebar';

			if ( 'left' == $shop_sidebar ) {
				$products_archive_classes[] = 'products-archive--sidebar-left';
			}

			// Sidebar order or mobile devices
			if ( kalium_get_theme_option( 'shop_sidebar_before_products_mobile' ) ) {
				$products_archive_classes[] = 'products-archive--sidebar-first';
			}
		}

		// Normal pagination
		$pagination_alignment       = kalium_get_theme_option( 'shop_pagination_position' );
		$products_archive_classes[] = 'products-archive--pagination-align-' . $pagination_alignment;

		?>
        <div <?php kalium_class_attr( $products_archive_classes ); ?>>

        <div class="products-archive--products">
		<?php
	}
}

/**
 * Archive wrapper after.
 */
if ( ! function_exists( 'kalium_woocommerce_archive_wrapper_end' ) ) {

	function kalium_woocommerce_archive_wrapper_end() {

		// Show on archive and product taxonomy page
		if ( ! ( is_shop() || is_product_taxonomy() ) ) {
			return;
		}

		?>
        </div><!-- .products-archive--products -->

		<?php if ( kalium_woocommerce_get_sidebar_position() ) : ?>

            <div class="products-archive--sidebar">

				<?php
				// Shop Widgets
				kalium_dynamic_sidebar( 'shop_sidebar', 'products-archive--widgets' );
				?>

            </div>

		<?php endif; ?>

        </div><!-- .products-archive -->

		<?php
	}
}

/**
 * Products loop wrapper start.
 */
if ( ! function_exists( 'kalium_woocommerce_product_loop_start' ) ) {

	function kalium_woocommerce_product_loop_start( $loop_wrapper ) {
		$loop_wrapper_classes = [ 'products-loop' ];

		// Enqueue Isotope and Packery
		kalium_enqueue_isotope_and_packery_library();

		if ( 'fitRows' == kalium_woocommerce_products_masonry_layout() ) {
			$loop_wrapper_classes[] = 'products-loop--fitrows';
		}

		return sprintf( '<div %s>%s', kalium_class_attr( $loop_wrapper_classes, false ), $loop_wrapper );
	}
}

/**
 * Products Loop wrapper end.
 */
if ( ! function_exists( 'kalium_woocommerce_product_loop_end' ) ) {

	function kalium_woocommerce_product_loop_end( $loop_wrapper ) {
		$loop_wrapper .= '</div>';

		return $loop_wrapper;
	}
}

/**
 * Single product images wrapper start.
 */
if ( ! function_exists( 'kalium_woocommerce_single_product_images_wrapper_start' ) ) {

	function kalium_woocommerce_single_product_images_wrapper_start() {

		// Gallery wrapper start
		echo '<div class="single-product-images">';

		// Kalium's default product image gallery
		do_action( 'kalium_woocommerce_single_product_images' );
	}
}

/**
 * Single product images wrapper end.
 */
if ( ! function_exists( 'kalium_woocommerce_single_product_images_wrapper_end' ) ) {

	function kalium_woocommerce_single_product_images_wrapper_end() {

		// Gallery wrapper end
		echo '</div>';
	}
}

/**
 * Get product image for Kalium image gallery.
 */
if ( ! function_exists( 'kalium_woocommerce_get_single_product_image' ) ) {

	function kalium_woocommerce_get_single_product_image( $attachment_id, $image_size, $lightbox_link = false ) {
		$full_image = wp_get_attachment_image_src( $attachment_id, 'full' );

		// If image doesn't exists
		if ( ! $full_image ) {
			return '';
		}

		$attributes = [
			'title'                   => get_post_field( 'post_title', $attachment_id ),
			'data-caption'            => get_post_field( 'post_excerpt', $attachment_id ),
			'data-src'                => $full_image[0],
			'data-large_image'        => $full_image[0],
			'data-large_image_width'  => $full_image[1],
			'data-large_image_height' => $full_image[2],
		];

		// Thumbnail
		$image = kalium_get_attachment_image( $attachment_id, $image_size, $attributes );

		// Product link image classes
		$product_link_classes = kalium()->helpers->list_classes( apply_filters( 'kalium_woocommerce_single_product_link_image_classes', [] ) );

		// HTML image object
		$html = '<div class="woocommerce-product-gallery__image">';
		$html .= sprintf( '<a href="%s" class="%s">', esc_url( $full_image[0] ), esc_attr( $product_link_classes ) );
		$html .= $image;
		$html .= '</a>';

		// Add image lightbox open link
		if ( $lightbox_link ) {
			$html .= kalium_woocommerce_get_lightbox_trigger_button( $attachment_id );
		}

		$html .= '</div>';

		return $html;
	}
}

/**
 *  WooCommerce Archive Header.
 */
if ( ! function_exists( 'kalium_woocommerce_archive_header' ) ) {

	function kalium_woocommerce_archive_header() {
		$show_title       = apply_filters( 'woocommerce_show_page_title', true );
		$show_ordering    = apply_filters( 'kalium_woocommerce_show_product_sorting', true );
		$show_shop_header = $show_title || $show_ordering;

		// Show on archive and product taxonomy page
		if ( ( is_shop() || is_product_taxonomy() ) && $show_shop_header ) {

			// Classes
			$classes = [
				'woocommerce-shop-header',
				'woocommerce-shop-header--columned',
			];
			?>
            <header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

				<?php if ( $show_title ) : ?>
                    <div class="woocommerce-shop-header--title woocommerce-products-header">

						<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

                            <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>

						<?php endif; ?>

						<?php
						/**
						 * Archive description below title
						 */
						do_action( 'kalium_woocommerce_archive_description' );
						?>

                    </div>
				<?php endif; ?>

				<?php if ( $show_ordering ) : ?>
                    <div class="woocommerce-shop-header--sorting">

						<?php
						/**
						 * Shop archive product sorting
						 */
						woocommerce_catalog_ordering();

						?>

                    </div>
				<?php endif; ?>

                <div class="woocommerce-shop-header--description">
					<?php
					do_action( 'woocommerce_archive_description' );
					?>
                </div>

            </header>
			<?php
		}
	}
}

/**
 * Ordering dropdown for products loop.
 */
if ( ! function_exists( 'kalium_woocommerce_shop_loop_ordering_dropdown' ) ) {

	function kalium_woocommerce_shop_loop_ordering_dropdown( $catalog_orderby_options, $orderby ) {
		$selected = '';
		$options  = '';

		foreach ( $catalog_orderby_options as $id => $name ) {
			$atts = '';

			if ( $orderby == $id ) {
				$selected = $name;
				$atts     = ' class="active"';
			}

			$options .= sprintf( '<li role="presentation"%3$s><a href="#%1$s">%2$s</a></li>', $id, esc_html( $name ), $atts );
		}

		?>
        <div class="woocommerce-ordering--dropdown form-group sort">

            <div class="dropdown">

                <button class="dropdown-toggle" type="button" data-toggle="dropdown">
                    <span><?php echo esc_html( $selected ); ?></span>
                    <i class="flaticon-bottom4"></i>
                </button>

                <ul class="dropdown-menu fade" role="menu">

					<?php
					/**
					 * Ordering options
					 */
					echo $options;
					?>

                </ul>

            </div>
        </div>
		<?php
	}
}

/**
 * Infinite pagination.
 */
if ( ! function_exists( 'kalium_woocommerce_infinite_scroll_pagination' ) ) {

	function kalium_woocommerce_infinite_scroll_pagination() {
		global $wp_query;

		// No products
		if ( $wp_query->is_main_query() && 0 === $wp_query->post_count ) {
			return;
		}

		// Disable infinite scroll pagination when WC_Prdctfltr pagination is used
		if ( class_exists( 'WC_Prdctfltr' ) && wc_string_to_bool( get_option( 'wc_settings_prdctfltr_use_ajax', 'no' ) ) && 'default' !== get_option( 'wc_settings_prdctfltr_pagination_type', 'default' ) ) {
			return;
		} else if ( class_exists( 'XforWC_Product_Filters' ) && wc_string_to_bool( get_option( 'wc_settings_prdctfltr_use_ajax', false ) ) ) {
			return;
		}

		// Pagination style and position
		$pagination_type     = kalium_get_theme_option( 'shop_pagination_type' );
		$pagination_style    = kalium_get_theme_option( 'shop_endless_pagination_style' );
		$pagination_position = kalium_get_theme_option( 'shop_pagination_position' );

		if ( in_array( $pagination_type, [ 'endless', 'endless-reveal' ] ) ) {

			// Args
			$args = [];

			$args['id']                   = 'products';
			$args['show_more_text']       = esc_html__( 'Show more', 'kalium' );
			$args['all_items_shown_text'] = esc_html__( 'No more products to show', 'kalium' );
			$args['loading_style']        = '_2' === $pagination_style ? 'pulsating' : 'spinner';

			// Endless pagination instance (JS)
			$query          = $GLOBALS['wp_query'];
			$posts_per_page = $query->query_vars['posts_per_page'];
			$found_posts    = absint( $query->found_posts );

			// No pagination
			if ( $found_posts <= $posts_per_page ) {
				return;
			}

			// Assign order and orderby value on infinite scroll for WooCommerce shop page.
			if ( ! isset( $query->query['orderby'] ) ) {
				$default_orderby         = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
				$query->query['orderby'] = $default_orderby;

				if ( ! empty( $wp_query->query_vars['order'] ) ) {
					$query->query['order'] = $wp_query->query_vars['order'];
				}
			}

			// Infinite scroll JS data
			$infinite_scroll_pagination_args = [

				// Base query
				'base_query'     => $query->query,

				// Pagination
				'total_items'    => $found_posts,
				'posts_per_page' => $posts_per_page,
				'fetched_items'  => kalium_get_post_ids_from_query( $query ),

				// Auto reveal
				'auto_reveal'    => 'endless-reveal' == $pagination_type,

				// Loop template function
				'loop_handler'   => '_kalium_woocommerce_infinite_scroll_pagination_handler',

				// Action and callback
				'callback'       => 'Kalium.WooCommerce.handleInfiniteScrollResponse',

				// Extra arguments (passed on Ajax Request)
				'args'           => [],
			];

			kalium_infinite_scroll_pagination_js_object( $args['id'], $infinite_scroll_pagination_args );

			// Infinite scroll button
			kalium_get_template( 'global/pagination-infinite-scroll.php', $args );

			// Remove pagination links
			remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
		}
	}
}

/**
 * Maybe show product categories.
 */
if ( ! function_exists( 'kalium_woocommerce_maybe_show_product_categories' ) ) {

	function kalium_woocommerce_maybe_show_product_categories() {
		wc_set_loop_prop( 'loop', 0 );

		$categories = woocommerce_maybe_show_product_subcategories( '' );

		if ( trim( $categories ) ) {
			$classes = [
				'products',
				'shop-categories',
				'columns-' . kalium_woocommerce_get_category_columns(),
			];

			echo sprintf( '<div class="%s">%s</div>', kalium()->helpers->list_classes( $classes, false ), $categories );
		}
	}
}

/**
 * Loop product images.
 */
if ( ! function_exists( 'kalium_woocommerce_catalog_loop_thumbnail' ) ) {

	function kalium_woocommerce_catalog_loop_thumbnail() {
		global $product;

		// Product images and settings
		$product_image_classes = [ 'product-images' ];
		$attachment_id         = get_post_thumbnail_id(); // featured image
		$attachment_ids        = $product->get_gallery_image_ids();

		if ( ! empty( $attachment_ids ) ) {
			$attachment_ids = array_unique( $attachment_ids );

			// Remove featured image fom attachments array
			$remove_index = array_search( $attachment_id, $attachment_ids );

			if ( false !== $remove_index ) {
				unset( $attachment_ids[ $remove_index ] );
			}
		}

		// Use images from gallery if there is no featured image assigned
		if ( false == has_post_thumbnail() ) {

			// Use an image from gallery if possible
			if ( false == empty( $attachment_ids ) ) {
				$attachment_id = array_shift( $attachment_ids );
			} else {
				$attachment_id = wc_placeholder_img_src();
			}
		}

		// Catalog thumbnails layout
		$shop_catalog_layout = kalium_woocommerce_get_catalog_layout();

		// Product info on hover
		$product_info_hover = in_array( $shop_catalog_layout, [
			'full-bg',
			'distanced-centered',
			'transparent-bg',
		] );

		if ( $product_info_hover ) {
			$product_image_classes[] = 'product-images--internal-details';
		}

		?>
        <div <?php kalium_class_attr( $product_image_classes ); ?>>

			<?php
			// Product featured image
			echo kalium_woocommerce_get_loop_product_image( $attachment_id );

			// Default product layout
			if ( 'default' == $shop_catalog_layout ) {

				if ( ! empty( $attachment_ids ) ) {

					$image_classes = [ 'gallery-image' ];

					// Image gallery type
					$image_gallery_type = kalium_get_theme_option( 'shop_item_preview_type' );

					// Second image on hover
					if ( 'fade' == $image_gallery_type ) {
						$second_image_id = array_shift( $attachment_ids );
						$image_classes[] = 'gallery-image--hoverable';

						echo kalium_woocommerce_get_loop_product_image( $second_image_id, $image_classes );
					} // Images gallery
					else if ( 'gallery' == $image_gallery_type ) {
						// Allowed gallery images
						if ( $max_gallery_images = apply_filters( 'kalium_woocommerce_catalog_default_gallery_images_length', 5 ) ) {
							if ( $max_gallery_images > 0 ) {
								$attachment_ids = array_slice( $attachment_ids, 0, $max_gallery_images - 1 );
							}
						}

						// Show images
						$image_classes[] = 'gallery-image--entry';

						foreach ( $attachment_ids as $gallery_image_id ) {
							echo kalium_woocommerce_get_loop_product_image( $gallery_image_id, $image_classes );
						}

						// Next and previous buttons
						echo '<a href="#" class="gallery-arrow gallery-prev"><i class="flaticon-arrow427"></i></a>';
						echo '<a href="#" class="gallery-arrow gallery-next"><i class="flaticon-arrow413"></i></a>';
					}
				}
			} // Full background, distanced background and transparent background
			else if ( $product_info_hover ) {

				echo '<div class="product-internal-info">';

				// Product info (hover layer)
				kalium_woocommerce_product_loop_item_info();

				echo '</div>';
			}
			?>

        </div>
		<?php
	}
}

/**
 * Get product image with product link.
 */
if ( ! function_exists( 'kalium_woocommerce_get_loop_product_image' ) ) {

	function kalium_woocommerce_get_loop_product_image( $attachment_id, $classes = [] ) {

		// Image size
		$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );

		// Get Image
		$image = kalium_get_attachment_image( $attachment_id, $image_size );

		// When there is no image
		if ( ! $image ) {
			return '';
		}

		ob_start();

		// Open link
		woocommerce_template_loop_product_link_open();

		// Show image
		echo $image;

		// Close link
		woocommerce_template_loop_product_link_close();

		$image_html = ob_get_clean();

		// Classes
		$classes = is_array( $classes ) || empty( $classes ) ? $classes : [ $classes ];

		if ( $classes ) {
			$classes    = kalium()->helpers->list_classes( $classes );
			$image_html = preg_replace( '/(woocommerce-LoopProduct-link.*?)\"/', '${1} ' . trim( $classes ) . '"', $image_html );
		}

		return $image_html;
	}
}

/**
 *  Loop product info.
 */
if ( ! function_exists( 'kalium_woocommerce_product_loop_item_info' ) ) {

	function kalium_woocommerce_product_loop_item_info() {
		global $woocommerce, $product, $post;

		$shop_catalog_layout = kalium_woocommerce_get_catalog_layout();

		#$cart_url = $woocommerce->cart->get_cart_url();
		$cart_url   = wc_get_page_permalink( 'cart' );
		$show_price = kalium_get_theme_option( 'shop_product_price_listing' );

		$shop_product_add_to_cart = kalium_get_theme_option( 'shop_add_to_cart_listing' );
		$shop_product_category    = kalium_get_theme_option( 'shop_product_category_listing' );

		// Product URL
		$product_url  = apply_filters( 'kalium_woocommerce_loop_product_link', get_permalink( $post ), $product );
		$link_new_tab = apply_filters( 'kalium_woocommerce_loop_product_link_new_tab', false, $product );

		// Full + Transparent Background Layout Type
		if ( in_array( $shop_catalog_layout, [ 'full-bg', 'transparent-bg' ] ) ) :
			?>
            <div class="item-info">

                <h3 <?php if ( $shop_catalog_layout == 'transparent-bg' && $shop_product_category == false ) : ?> class="no-category-present"<?php endif; ?>>
                    <a href="<?php echo $product_url; ?>"<?php when_match( $link_new_tab, ' target="_blank" rel="noopener"' ); ?>><?php the_title(); ?></a>
                </h3>

				<?php
				/**
				 * Filters after product title on loop view.
				 *
				 * Hook: kalium_woocommerce_product_loop_after_title.
				 */
				do_action( 'kalium_woocommerce_product_loop_after_title' );
				?>

				<?php if ( $shop_product_category ) : ?>
                    <div class="product-terms">
						<?php echo wc_get_product_category_list( $product->get_id() ); ?>
                    </div>
				<?php endif; ?>


                <div class="product-bottom-details">

					<?php if ( $show_price ) : ?>
                        <div class="price-column">
							<?php woocommerce_template_loop_price(); ?>
                        </div>
					<?php endif; ?>

					<?php if ( false == kalium_woocommerce_is_catalog_mode() ) : ?>
                        <div class="add-to-cart-column">
							<?php
							// Add to cart
							woocommerce_template_loop_add_to_cart();

							// Add to cart icons
							if ( $shop_product_add_to_cart ) {
								kalium_woocommerce_add_to_cart_icons();
							}
							?>
                        </div>
					<?php endif; ?>

                </div>

            </div>
		<?php

		// Centered – Distanced Background Layout Type
        elseif ( in_array( $shop_catalog_layout, [ 'distanced-centered' ] ) ) :

			?>
            <div class="item-info">

                <div class="title-and-price">

                    <h3>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

					<?php
					/**
					 * Filters after product title on loop view.
					 *
					 * Hook: kalium_woocommerce_product_loop_after_title.
					 */
					do_action( 'kalium_woocommerce_product_loop_after_title' );
					?>

					<?php if ( $show_price ) : woocommerce_template_loop_price(); endif; ?>

                </div>

                <div class="add-to-cart-link-holder">
					<?php woocommerce_template_loop_add_to_cart(); ?>
                </div>

            </div>
		<?php

		else :

			?>
            <div class="item-info">

                <div class="item-info-row">

                    <div class="title-column">
                        <h3>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

						<?php
						/**
						 * Filters after product title on loop view
						 */
						do_action( 'kalium_woocommerce_product_loop_after_title' );

						/**
						 * Product terms and add to cart button.
						 */
						if ( $shop_product_add_to_cart || $shop_product_category ) {
							$categories_add_to_cart_container = [ 'add-to-cart-and-product-categories' ];

							if ( $shop_product_category && $shop_product_add_to_cart ) {
								$categories_add_to_cart_container[] = 'show-add-to-cart';
							}
							?>
                            <div <?php kalium_class_attr( $categories_add_to_cart_container ); ?>>
								<?php
								kalium_woocommerce_product_loop_categories_default_layout();

								if ( $shop_product_add_to_cart ) {
									woocommerce_template_loop_add_to_cart();
								}
								?>
                            </div>
							<?php
						}
						?>
                    </div>

					<?php if ( $show_price ) : ?>
                        <div class="price-column">
							<?php woocommerce_template_loop_price(); ?>
                        </div>
					<?php endif; ?>
                </div>

            </div>


            <div class="added-to-cart-button">
                <a href="<?php echo $cart_url; ?>"><i class="icon icon-ecommerce-bag-check"></i></a>
            </div>
		<?php

		endif;
	}
}

/**
 * Show product terms in Default Product Layout.
 */
if ( ! function_exists( 'kalium_woocommerce_product_loop_categories_default_layout' ) ) {

	function kalium_woocommerce_product_loop_categories_default_layout() {
		global $product;

		if ( kalium_get_theme_option( 'shop_product_category_listing' ) ) {
			$classes = [ 'product-terms' ];
			?>
            <div <?php kalium_class_attr( $classes ); ?>>
				<?php echo wc_get_product_category_list( $product->get_id() ); ?>
            </div>
			<?php
		}
	}
}

/**
 * Add to cart icons.
 */
if ( ! function_exists( 'kalium_woocommerce_add_to_cart_icons' ) ) {

	function kalium_woocommerce_add_to_cart_icons() {
		global $product;

		$icon_class = 'icon-ecommerce-bag-plus';

		// Variable product
		if ( in_array( $product->get_type(), [ 'variable', 'external' ] ) ) {
			$icon_class = 'icon-ecommerce-bag';
		}

		// Add to cart icon
		printf( '<i class="add-to-cart-icon %s"></i>', esc_attr( $icon_class ) );

		// Added to cart icon
		if ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			echo '<i class="added-to-cart-icon icon-ecommerce-bag-check"></i>';
		}
	}
}

/**
 * Product Images Layout in Single Product page.
 */
if ( ! function_exists( 'kalium_woocommerce_show_product_images_custom_layout' ) ) {

	function kalium_woocommerce_show_product_images_custom_layout( $images_layout_type = 'carousel' ) {
		global $post, $product;

		// Attachments
		$attachment_ids                    = $product->get_gallery_image_ids();
		$shop_single_product_images_layout = kalium_get_theme_option( 'shop_single_product_images_layout' );

		$images_container_classes   = [ 'kalium-woocommerce-product-gallery' ];
		$images_container_classes[] = "images-layout-type-{$shop_single_product_images_layout}";

		// Is Carousel Type
		$is_carousel = true;

		// Toggles
		$zoom_enabled     = kalium_woocommerce_is_product_gallery_zoom_enabled();
		$lightbox_enabled = kalium_woocommerce_is_product_gallery_lightbox_enabled();

		// Carousel autoplay
		$shop_single_auto_rotate_image = kalium_get_theme_option( 'shop_single_auto_rotate_image' );

		// Product image setup options
		$single_product_params_js = [
			'zoom' => [
				'enabled' => $zoom_enabled,
				'options' => [
					'magnify' => 1,
				],
			],

			'lightbox' => [
				'enabled' => $lightbox_enabled,
				'options' => [
					'shareEl'               => false,
					'closeOnScroll'         => false,
					'history'               => false,
					'hideAnimationDuration' => 0,
					'showAnimationDuration' => 0,
				],
			],

			'carousel' => [
				'autoPlay' => is_numeric( $shop_single_auto_rotate_image ) ? intval( $shop_single_auto_rotate_image ) : 5,
			],
		];

		// Thumbnail columns
		$thumbnails_columns = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );

		// Images Carousel
		$images_carousel_classes = [ 'main-product-images' ];

		// Enqueue carousel library
		if ( in_array( $shop_single_product_images_layout, [ 'plain', 'plain-sticky' ] ) ) {
			$is_carousel = false; // not carousel product images

			$images_carousel_classes[] = 'plain';

			// Stretch images to browser edge	
			if ( 'yes' == kalium_get_theme_option( 'shop_single_plain_image_stretch' ) ) {
				$images_carousel_classes[] = 'stretched-image';
				$images_carousel_classes[] = 'right' == kalium_get_theme_option( 'shop_single_image_alignment' ) ? 'right-edge-sticked' : 'left-edge-sticked';
			}

			// Enable carousel on mobile
			if ( apply_filters( 'kalium_woocommerce_single_product_plain_images_carousel_mobile', true ) ) {
				$images_carousel_classes[] = 'plain-images-carousel-mobile';

				// Enqueue carousel library
				kalium_enqueue_flickity_library();
			}

			// Add animation for plain type
			add_filter( 'kalium_woocommerce_single_product_link_image_classes', '_kalium_woocommerce_single_product_link_image_classes_plain' );
		} else {
			$images_carousel_classes[] = 'carousel';

			// Enqueue carousel library
			kalium_enqueue_flickity_library();

			// Fade transition
			if ( 'fade' === kalium_get_theme_option( 'shop_single_image_carousel_transition_type' ) ) {
				kalium_enqueue_flickity_fade_library();
			}

			// Add animation for carousel type
			add_filter( 'kalium_woocommerce_single_product_link_image_classes', '_kalium_woocommerce_single_product_link_image_classes_carousel' );
		}

		// Product gallery is sticky
		if ( 'plain-sticky' == $shop_single_product_images_layout ) {
			$images_carousel_classes[] = 'sticky';
		}

		// When lightbox is enabled
		if ( $lightbox_enabled ) {
			$images_carousel_classes[] = 'has-lightbox';
		}

		// Populate Images Array
		$images = [];

		// Featured image first
		if ( has_post_thumbnail() ) {
			$images[] = get_post_thumbnail_id( $product->get_id() );
		}

		// Gallery images
		$images = array_merge( $images, $attachment_ids );

		// Carousel Skip Featured Image
		$carousel_skip_featured_image = true === apply_filters( 'kalium_woocommerce_skip_featured_image_in_carousel', false );

		if ( $is_carousel && $carousel_skip_featured_image ) {
			$images_carousel_classes[] = 'skip-featured-image';
		}

		// No Spacing for carousel images
		if ( apply_filters( 'kalium_woocommerce_single_product_images_carousel_no_spacing', false ) ) {
			$images_carousel_classes[] = 'no-spacing';
		}

		// Show product images
		?>
        <div <?php kalium_class_attr( $images_container_classes ); ?>>

            <div <?php kalium_class_attr( $images_carousel_classes ); ?>>

				<?php
				// Image sizes
				$size_shop_single    = kalium_woocommerce_get_product_image_size( 'single' );
				$size_shop_thumbnail = kalium_woocommerce_get_product_image_size( 'gallery' );

				// Show images
				if ( count( $images ) ) :

					foreach ( $images as $i => $attachment_id ) :

						$full_size_image = wp_get_attachment_image_src( $attachment_id, 'full' );

						$html = kalium_woocommerce_get_single_product_image( $attachment_id, $size_shop_single, $lightbox_enabled );

						echo apply_filters( 'kalium_woocommerce_single_product_image_html', $html, $attachment_id );

					endforeach;


				// Show placeholder
				else :

					$html = kalium_get_attachment_image( wc_placeholder_img_src() );

					echo apply_filters( 'kalium_woocommerce_single_product_image_placeholder_html', $html );

				endif;
				?>

            </div>

			<?php
			// Product thumbnails	
			if ( $is_carousel ) :

				// Skip featured image
				if ( $carousel_skip_featured_image ) {
					$images = array_slice( $images, 1, count( $images ) - 1 );
				}

				if ( count( $images ) > 1 ) :
					?>
                    <div class="thumbnails" data-columns="<?php echo esc_attr( $thumbnails_columns ); ?>">
						<?php

						foreach ( $images as $attachment_id ) :

							$html = kalium_woocommerce_get_single_product_image( $attachment_id, $size_shop_thumbnail );

							echo apply_filters( 'kalium_woocommerce_single_product_image_html', $html, $attachment_id );

						endforeach;

						?>
                    </div>
				<?php
				endif;

			endif;
			?>

            <script type="text/template" class="product-params-js">
				<?php
				// Single product params (JS var)
				echo wp_json_encode( apply_filters( 'kalium_woocommerce_single_product_params_js', $single_product_params_js ) );
				?>
            </script>
        </div>
		<?php
	}
}

/**
 * Render product rating.
 */
if ( ! function_exists( 'kalium_woocommerce_show_rating' ) ) {

	function kalium_woocommerce_show_rating( $average ) {
		$average = (float) $average;
		$shop_single_rating_style = kalium_get_theme_option( 'shop_single_rating_style' );
		?>
        <div class="star-rating-icons" data-toggle="tooltip" data-placement="right"
             title="<?php echo sprintf( __( '%s out of 5', 'kalium' ), $average ); ?>">
			<?php

			$average_int     = intval( $average );
			$average_floated = $average - $average_int;

			for ( $i = 1; $i <= 5; $i ++ ) :

				if ( in_array( $shop_single_rating_style, [ 'circles', 'rectangles' ] ) ) :

					$fill = 100;

					if ( $i > $average ) {
						$fill = 0;

						if ( $average_int + 1 == $i ) {
							$fill = $average_floated * 100;
						}
					}
					?>
                    <span class="circle<?php echo $shop_single_rating_style == 'circles' ? ' rounded' : ''; ?>">
					<i style="width: <?php echo esc_attr( $fill ); ?>%"></i>
				</span>
				<?php
				else:
					?>
                    <i class="fa fa-star<?php echo round( $average ) >= $i ? ' filled' : ''; ?>"></i>
				<?php
				endif;

			endfor;

			?>
        </div>
		<?php
	}
}

/**
 * My account wrapper (before).
 */
if ( ! function_exists( 'kalium_woocommerce_my_account_wrapper_start' ) ) {

	function kalium_woocommerce_my_account_wrapper_start() {
		echo '<div class="my-account">';
	}
}

/**
 * My account wrapper (after).
 */
if ( ! function_exists( 'kalium_woocommerce_my_account_wrapper_end' ) ) {

	function kalium_woocommerce_my_account_wrapper_end() {
		echo '</div>'; // .my-account
	}
}

/**
 * Review rating.
 */
if ( ! function_exists( 'kalium_woocommerce_product_get_rating_html' ) ) {

	function kalium_woocommerce_product_get_rating_html( $html, $rating, $count ) {
		ob_start();
		?>
        <div class="star-rating">
			<?php
			kalium_woocommerce_show_rating( $rating );
			?>
        </div>
		<?php

		return ob_get_clean();
	}
}

/**
 * Product rating.
 */
if ( ! function_exists( 'kalium_woocommerce_single_product_rating_stars' ) ) {

	function kalium_woocommerce_single_product_rating_stars() {
		global $product;

		$average = $product->get_average_rating();
		$title   = sprintf( esc_attr__( 'Rated %s out of 5', 'kalium' ), $average );

		?>
        <div class="star-rating" title="<?php echo esc_attr( $title ); ?>">
			<?php kalium_woocommerce_show_rating( $average ); ?>
        </div>
		<?php
	}
}

/**
 * Payment method title.
 */
if ( ! function_exists( 'kalium_woocommerce_review_order_before_payment_title' ) ) {

	function kalium_woocommerce_review_order_before_payment_title() {
		?>
        <h2 id="payment_method_heading"><?php esc_html_e( 'Payment method', 'kalium' ); ?></h2>
		<?php
	}
}

/**
 * Login page heading.
 */
if ( ! function_exists( 'kalium_woocommerce_my_account_login_page_heading' ) ) {

	function kalium_woocommerce_my_account_login_page_heading() {

		?>
        <div class="section-title">

            <h1>
				<?php
				if ( kalium_validate_boolean( get_option( 'woocommerce_enable_myaccount_registration' ) ) ) {
					esc_html_e( 'Login or register', 'kalium' );
				} else {
					esc_html_e( 'Login', 'kalium' );
				}
				?>
            </h1>

            <p><?php esc_html_e( 'Manage your account and see your orders', 'kalium' ) ?></p>
        </div>

		<?php
	}
}

/**
 * Show mini cart icon and contents in header.
 */
if ( ! function_exists( 'kalium_woocommerce_header_mini_cart' ) ) {

	function kalium_woocommerce_header_mini_cart( $skin ) {
		if ( kalium_get_theme_option( 'shop_cart_icon_menu' ) ) {
			kalium_woocommerce_cart_menu_icon( $skin );
		}
	}
}

/**
 * Cart menu icon.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_menu_icon' ) ) {

	function kalium_woocommerce_cart_menu_icon( $skin, $dropdown_align = 'left' ) {
		$icon               = kalium_get_theme_option( 'shop_cart_icon' );
		$hide_empty         = kalium_get_theme_option( 'shop_cart_icon_menu_hide_empty' );
		$show_cart_contents = kalium_get_theme_option( 'shop_cart_contents' );
		$cart_items_counter = kalium_get_theme_option( 'shop_cart_icon_menu_count' );

		$cart_items = WC()->cart->get_cart_contents_count();

		if ( ! in_array( $dropdown_align, [ 'left', 'center', 'right' ] ) ) {
			$dropdown_align = 'left';
		}

		$classes = [
			'menu-cart-icon-container',
		];

		// Skin
		$classes[] = $skin;

		// Hide cart icon when empty
		if ( $hide_empty && ! $cart_items ) {
			$classes[] = 'menu-cart-icon-container--hide-empty';
		}

		// Show on hover
		if ( 'show-on-hover' === $show_cart_contents ) {
			$classes[] = 'hover-show';
		}

		// Dropdown alignment
		$classes[] = 'menu-cart-icon-container--dropdown-align-' . $dropdown_align;

		?>
        <div <?php kalium_class_attr( $classes ); ?>>

            <a href="<?php echo wc_get_cart_url(); ?>"
               class="cart-icon-link icon-type-<?php echo esc_attr( $icon ); ?>">
                <i class="icon-<?php echo esc_attr( $icon ); ?>"></i>

				<?php if ( $cart_items_counter ) : ?>
                    <span class="items-count hide-notification cart-items-<?php echo esc_attr( $cart_items ); ?>">&hellip;</span>
				<?php endif; ?>
            </a>


			<?php if ( $show_cart_contents != 'hide' ) : ?>
                <div class="lab-wc-mini-cart-contents">
					<?php get_template_part( 'tpls/wc-mini-cart' ); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php
	}
}

/**
 * Mobile cart icon on menu.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_menu_icon_mobile' ) ) {

	function kalium_woocommerce_cart_menu_icon_mobile() {
		$icon               = kalium_get_theme_option( 'shop_cart_icon' );
		$hide_empty         = kalium_get_theme_option( 'shop_cart_icon_menu_hide_empty' );
		$show_cart_contents = kalium_get_theme_option( 'shop_cart_contents' );
		$cart_items_counter = kalium_get_theme_option( 'shop_cart_icon_menu_count' );

		$cart_items = WC()->cart->get_cart_contents_count();

		?>
        <div class="cart-icon-link-mobile-container">
            <a href="<?php echo wc_get_cart_url(); ?>"
               class="cart-icon-link-mobile icon-type-<?php echo esc_attr( $icon ); ?>">
                <i class="icon icon-<?php echo esc_attr( $icon ); ?>"></i>

				<?php esc_html_e( 'Cart', 'kalium' ); ?>

				<?php if ( $cart_items_counter ) : ?>
                    <span class="items-count hide-notification cart-items-<?php echo esc_attr( $cart_items ); ?>">&hellip;</span>
				<?php endif; ?>
            </a>
        </div>
		<?php
	}
}

/**
 * Cart totals widget used on header.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_totals_widget' ) ) {

	function kalium_woocommerce_cart_totals_widget( $args = [] ) {

		// If WooCommerce is not installed
		if ( ! kalium()->is->woocommerce_active() ) {
			return;
		}

		// Args
		$args = wp_parse_args( $args, [
			'total_price' => '',
			'text_before' => '',
			'hide_empty'  => true,
			'skin'        => null,
		] );

		// Totals
		$totals = WC()->cart->get_totals();

		switch ( $args['total_price'] ) {

			// Sub total
			case 'cart-subtotal':
				$total_display_price = $totals['subtotal'];
				break;

			// Cart total excluding tax
			case 'cart-total-ex-tax':
				$total_display_price = $totals['total'] - $totals['total_tax'];
				break;

			// Default total price
			default:
				$total_display_price = $totals['total'];
		}

		// Classes
		$classes = [
			'cart-totals-widget',
			$args['skin'],
		];

		// Hide when empty
		if ( $args['hide_empty'] ) {
			$classes[] = 'cart-totals-widget--hide-empty';

			if ( 0 === WC()->cart->get_cart_contents_count() ) {
				$classes[] = 'cart-totals-widget--hidden';
			}
		}

		?>
        <div <?php kalium_class_attr( $classes ); ?> data-total-price="<?php echo esc_attr( $args['total_price'] ? $args['total_price'] : 'cart-total' ); ?>">

			<?php if ( $args['text_before'] ) : ?>
                <span class="text-before">
                    <?php echo esc_html( $args['text_before'] ); ?>
                </span>
			<?php endif; ?>


			<?php
			// Price total
			kalium()->helpers->build_dom_element( [
				'tag_name'   => 'a',
				'attributes' => [
					'href'  => wc_get_cart_url(),
					'class' => 'cart-total',
				],
				'content'    => wc_price( $total_display_price ), // Wrap price with currency
				'echo'       => true,
			] );
			?>

        </div>
		<?php
	}
}

/**
 * Product sharing.
 */
if ( ! function_exists( 'kalium_woocommerce_share_product' ) ) {

	function kalium_woocommerce_share_product() {
		global $product;

		$rounded_icons = kalium_get_theme_option( 'shop_share_product_rounded_icons' );
		?>
        <div class="share-product-container">
            <h3><?php _e( 'Share this item:', 'kalium' ); ?></h3>

            <div class="share-product social-links<?php when_match( ! $rounded_icons, 'textual' ) ?>">
				<?php

				$share_product_networks = kalium_get_theme_option( 'shop_share_product_networks' );

				if ( is_array( $share_product_networks ) ) :

					foreach ( $share_product_networks['visible'] as $network_id => $network ) :

						if ( 'placebo' == $network_id ) {
							continue;
						}

						kalium_social_network_share_post_link( $network_id, $product->get_id(), [
							'icon_only' => $rounded_icons ? true : false,
						] );

					endforeach;

				endif;

				?>
            </div>
        </div>
		<?php
	}
}

/**
 * Account Navigation (before).
 */
if ( ! function_exists( 'kalium_woocommerce_account_navigation_before' ) ) {

	function kalium_woocommerce_account_navigation_before() {
		global $current_user;

		$account_page_id = wc_get_page_id( 'myaccount' );
		$account_url     = get_permalink( $account_page_id );

		?>
        <div class="wc-my-account-tabs">

        <div class="user-profile">
            <a class="image">
				<?php echo get_avatar( $current_user->ID, 128 ); ?>
            </a>
            <div class="user-info">
                <a class="name"
                   href="<?php echo the_author_meta( 'user_url', $current_user->ID ); ?>"><?php echo $current_user->display_name; ?></a>
            </div>
        </div>
		<?php
	}
}

/**
 * Account Navigation (after).
 */
if ( ! function_exists( 'kalium_woocommerce_account_navigation_after' ) ) {

	function kalium_woocommerce_account_navigation_after() {
		?>
        </div><!-- .wc-my-account-tabs -->
		<?php
	}
}

/**
 *  My Orders Page Title
 */
if ( ! function_exists( 'kalium_woocommerce_before_account_orders' ) ) {

	function kalium_woocommerce_before_account_orders( $has_orders ) {

		?>
        <div class="section-title">
            <h1><?php esc_html_e( 'My Orders', 'kalium' ); ?></h1>
            <p><?php esc_html_e( 'Your recent orders are displayed in the table below.', 'kalium' ); ?></p>
        </div>
		<?php
	}
}

/**
 * My Downloads page title.
 */
if ( ! function_exists( 'kalium_woocommerce_before_account_downloads' ) ) {

	function kalium_woocommerce_before_account_downloads( $has_orders ) {

		?>
        <div class="section-title">
            <h1><?php esc_html_e( 'My Downloads', 'kalium' ); ?></h1>
            <p><?php esc_html_e( 'Your digital downloads are displayed in the table below.', 'kalium' ); ?></p>
        </div>
		<?php
	}
}

/**
 * Trigger lightbox button.
 */
if ( ! function_exists( 'kalium_woocommerce_get_lightbox_trigger_button' ) ) {

	function kalium_woocommerce_get_lightbox_trigger_button( $attachment_id ) {
		return sprintf( '<button class="product-gallery-lightbox-trigger" data-id="%s" title="%s"><i class="flaticon-close38"></i></button>', $attachment_id, esc_attr__( 'View full size', 'kalium' ) );
	}
}

/**
 * Single product images alignment.
 */
if ( ! function_exists( 'kalium_woocommerce_get_product_gallery_container_alignment' ) ) {

	function kalium_woocommerce_get_product_gallery_container_alignment() {
		$image_alignment = kalium_get_theme_option( 'shop_single_image_alignment' );

		return 'right' === $image_alignment ? 'right' : 'left';
	}
}

/**
 * BACS details before.
 */
if ( ! function_exists( 'kalium_woocommerce_bacs_details_before' ) ) {

	function kalium_woocommerce_bacs_details_before() {
		echo '<div class="bacs-details-container">';
	}
}

/**
 * BACS details after.
 */
if ( ! function_exists( 'kalium_woocommerce_bacs_details_after' ) ) {

	function kalium_woocommerce_bacs_details_after() {
		echo '</div>'; // .bacs-details-container
	}
}

/**
 * Show rating below top rated products widget.
 */
if ( ! function_exists( 'kalium_woocommerce_top_rated_products_widget_rating' ) ) {

	function kalium_woocommerce_top_rated_products_widget_rating( $args ) {
		global $product;

		if ( ! empty( $args['show_rating'] ) && $product->get_average_rating() ) :
			?>
            <p class="rating">
                <i class="fa fa-star"></i>
				<?php echo $product->get_average_rating(); ?>
            </p>
		<?php
		endif;
	}
}

/**
 * Single product wrapper start.
 */
if ( ! function_exists( 'kalium_woocommerce_single_product_wrapper_start' ) ) {

	function kalium_woocommerce_single_product_wrapper_start() {
		$sidebar = kalium_woocommerce_single_product_get_sidebar_position();
		$classes = [
			'single-product',
		];

		if ( $sidebar ) {
			$classes[] = 'single-product--has-sidebar';

			if ( 'left' == $sidebar ) {
				$classes[] = 'single-product--sidebar-left';
			}

			if ( kalium_get_theme_option( 'shop_single_sidebar_before_products_mobile' ) ) {
				$classes[] = 'single-product--sidebar-first';
			}
		}

		?>
        <div <?php kalium_class_attr( $classes ); ?>>

        <div class="single-product--product-details">
		<?php
	}
}

/**
 * Single product wrapper end.
 */
if ( ! function_exists( 'kalium_woocommerce_single_product_wrapper_end' ) ) {

	function kalium_woocommerce_single_product_wrapper_end() {

		?>
        </div><!-- .single-product--product-details -->

		<?php
		// Sidebar
		if ( kalium_woocommerce_single_product_get_sidebar_position() ) :

			?>
            <div class="single-product--sidebar">
				<?php
				// Show widgets
				$sidebar = is_active_sidebar( 'shop_sidebar_single' ) ? 'shop_sidebar_single' : 'shop_sidebar';

				kalium_dynamic_sidebar( $sidebar, 'single-product--widgets' );
				?>
            </div>
		<?php
		endif;
		?>

        </div><!-- .single-product -->
		<?php
	}
}

/**
 * Product flash badges.
 */
if ( ! function_exists( 'kalium_woocommerce_product_badges' ) ) {

	function kalium_woocommerce_product_badges() {
		global $post, $product;

		$html = '';

		// Out of stock
		if ( ( $product->is_in_stock() == false && ! ( $product->is_type( 'variable' ) && $product->get_stock_quantity() > 0 ) ) && kalium_get_theme_option( 'shop_oos_ribbon_show' ) ) {
			$html = sprintf( '<div class="onsale oos">%s</div>', esc_html__( 'Out of stock', 'kalium' ) );
		} // Featured product
		else if ( $product->is_featured() && kalium_get_theme_option( 'shop_featured_ribbon_show' ) ) {
			$html = sprintf( '<span class="onsale featured">%s</span>', esc_html__( 'Featured', 'kalium' ) );
		} // Sale
		else if ( $product->is_on_sale() && kalium_get_theme_option( 'shop_sale_ribbon_show' ) ) {
			$html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'woocommerce' ) . '</span>', $post, $product );
		}

		echo $html;
	}
}

/**
 * Edit address sub title.
 */
if ( ! function_exists( 'kalium_woocommerce_my_account_edit_address_title' ) ) {

	function kalium_woocommerce_my_account_edit_address_title( $title ) {
		return sprintf( '%s<small>%s</small>', $title, esc_html__( 'Edit information for this address type', 'kalium' ) );
	}
}

/**
 * Address wrapper on "My Account" page.
 */
if ( ! function_exists( 'kalium_woocommerce_my_account_my_address_description' ) ) {

	function kalium_woocommerce_my_account_my_address_description( $subtitle ) {
		$title    = sprintf( '<strong class="my-address-title">%s</strong>', esc_html__( 'Addresses', 'woocommerce' ) );
		$subtitle = sprintf( '<span class="my-address-subtitle">%s</span>', $subtitle );

		return $title . $subtitle;
	}
}

/**
 * WooCommerce Go back link.
 */
if ( ! function_exists( 'kalium_woocommerce_go_back_link' ) ) {

	function kalium_woocommerce_go_back_link( $link, $title = '' ) {
		if ( empty( $title ) ) {
			$title = esc_html__( '&laquo; Go back', 'kalium' );
		}

		echo sprintf( '<a href="%s" class="go-back-link">%s</a>', esc_url( $link ), esc_html( $title ) );
	}
}

/**
 * Edit account heading.
 */
if ( ! function_exists( 'kalium_woocommerce_myaccount_edit_account_heading' ) ) {

	function kalium_woocommerce_myaccount_edit_account_heading() {
		?>
        <div class="section-title">
            <h1><?php esc_html_e( 'My account', 'kalium' ) ?></h1>
            <p><?php esc_html_e( 'Edit your account details or change your password', 'kalium' ); ?></p>
        </div>
		<?php
	}
}

/**
 * Go back link for Edit Address page.
 */
if ( ! function_exists( 'kalium_woocommerce_myaccount_edit_address_back_link' ) ) {

	function kalium_woocommerce_myaccount_edit_address_back_link() {
		kalium_woocommerce_go_back_link( wc_get_account_endpoint_url( 'edit-address' ) );
	}
}

/**
 * Go back link for Forgot Password page.
 */
if ( ! function_exists( 'kalium_woocommerce_myaccount_forgot_password_back_link' ) ) {

	function kalium_woocommerce_myaccount_forgot_password_back_link() {
		kalium_woocommerce_go_back_link( wc_get_account_endpoint_url( 'dashboard' ) );
	}
}
/**
 * Empty cart text.
 */
if ( ! function_exists( 'kalium_woocommerce_empty_cart_message' ) ) {

	function kalium_woocommerce_empty_cart_message() {

		?>
        <div class="cart-empty">

            <div class="cart-empty__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="55.609" height="66.789" viewBox="0 0 55.609 66.789">
                    <g id="shopping-bag-sad" transform="translate(1.25 1.25)">
                        <path id="Path_34" data-name="Path 34" d="M8.2,14.5a.7.7,0,1,1-.7.7.7.7,0,0,1,.7-.7" transform="translate(6.484 24.632)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        <path id="Path_35" data-name="Path 35" d="M16.7,14.5a.7.7,0,1,1-.7.7.7.7,0,0,1,.7-.7" transform="translate(21.743 24.632)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        <path id="Path_36" data-name="Path 36" d="M8.257,23.093a12.578,12.578,0,0,1,20.922,0" transform="translate(7.843 30.016)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        <path id="Path_37" data-name="Path 37" d="M7.5,25.656V13.078a12.578,12.578,0,1,1,25.156,0V25.656" transform="translate(6.484 -0.5)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        <path id="Path_38" data-name="Path 38" d="M55.519,45.632,53,9.3a3,3,0,0,0-2.988-2.8H8.1A3,3,0,0,0,5.108,9.3L2.592,45.632q-.1,1.4-.1,2.8a5.59,5.59,0,0,0,5.59,5.59H50.015a5.59,5.59,0,0,0,5.59-5.59Q55.614,47.03,55.519,45.632Z" transform="translate(-2.497 10.271)" fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                    </g>
                </svg>
            </div>

            <h1 class="cart-empty__title">
				<?php esc_html_e( 'There is no product in the cart', 'kalium' ); ?>
            </h1>

            <p class="cart-empty__message">
				<?php esc_html_e( 'Browse our products and add products to your cart', 'kalium' ); ?>
            </p>

        </div>
		<?php
	}
}

/**
 * Cart heading.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_heading' ) ) {

	function kalium_woocommerce_cart_heading() {

		// Hide cart heading when default heading title is enabled
		if ( kalium_get_field( 'heading_title', get_queried_object_id() ) ) {
			return;
		}

		// Number of items in cart
		$cart_items = WC()->cart->get_cart_contents_count();

		// Template args
		$args = [
			'heading_tag' => 'h1',
			'title'       => get_the_title(),
			'description' => sprintf( _n( 'You\'ve got 1 item in the cart', 'You\'ve got %d items in the cart', $cart_items, 'kalium' ), $cart_items ),
		];

		// Load page heading template
		kalium_get_template( 'global/page-heading.php', $args );
	}
}

/**
 * Cart wrapper start.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_wrapper_start' ) ) {

	function kalium_woocommerce_cart_wrapper_start() {
		?>
        <div class="cart-wrapper">
		<?php
	}
}

/**
 * Cart wrapper end.
 */
if ( ! function_exists( 'kalium_woocommerce_cart_wrapper_end' ) ) {

	function kalium_woocommerce_cart_wrapper_end() {
		?>
        </div>
		<?php
	}
}
