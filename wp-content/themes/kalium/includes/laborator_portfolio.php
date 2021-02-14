<?php
/**
 *    Kalium WordPress Theme
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Location Rules for Portfolio Item Types
 */
function laborator_acf_location_rules_types( $choices ) {
	$choices['Other']['portfolio_item_type'] = 'Portfolio Item Type';

	return $choices;
}

add_filter( 'acf/location/rule_types', 'laborator_acf_location_rules_types' );

function laborator_acf_location_rules_match_field_groups( $field_groups = array(), $options = array() ) {

	// Match Portfolio Item Type Group Fields
	if ( isset( $options['item_type'] ) ) {
		$post_id = $options['post_id'];

		// Update Current Portfolio Item Type
		$current_item_type = kalium_get_field( 'item_type', $post_id );
		update_field( 'item_type', $options['item_type'], $post_id );

		// Match New Rules
		$acf_location = new acf_location();
		$field_groups = $acf_location->match_field_groups( array(), $options );

		// Revert Back the Current Type
		if ( empty( $current_item_type ) ) {
			delete_field( 'item_type', $post_id );
		} else {
			update_field( 'item_type', $current_item_type, $post_id );
		}
	}

	return $field_groups;
}

add_filter( 'acf/location/match_field_groups', 'laborator_acf_location_rules_match_field_groups', 10, 3 ); // Removed in ACF5

function laborator_acf_location_rules_values_item_type( $choices ) {
	$portfolio_item_types = array(
		'type-1' => 'Side Portfolio',
		'type-2' => 'Columned',
		'type-3' => 'Carousel',
		'type-4' => 'Zig Zag',
		'type-5' => 'Fullscreen',
		'type-6' => 'Lightbox',
		'type-7' => 'WPB Page Builder',
	);

	return $portfolio_item_types;
}

add_filter( 'acf/location/rule_values/portfolio_item_type', 'laborator_acf_location_rules_values_item_type' );

function laborator_acf_location_rules_item_type( $match, $rule, $options ) {
	$rule_item_type = $rule['value'];

	if ( ! empty( $options['post_id'] ) ) {
		// Current Post
		$current_post = get_post( $options['post_id'] );
		$item_type    = $current_post->item_type;

		if ( version_compare( acf()->version, '4.4.12', '>' ) && isset( $options['item_type'] ) ) {
			$item_type = $options['item_type'];
		}

		if ( $rule['operator'] == "==" ) {
			return $rule_item_type == $item_type;
		}
	}
}

add_filter( 'acf/location/rule_match/portfolio_item_type', 'laborator_acf_location_rules_item_type', 10, 3 );

/**
 * Portfolio Like Column
 */
function laborator_portfolio_like_column( $columns ) {
	$last_column = array_keys( $columns );
	$last_column = end( $last_column );

	$last_column_title = end( $columns );

	unset( $columns[ $last_column ] );

	$columns['likes']        = 'Likes';
	$columns[ $last_column ] = $last_column_title;

	return $columns;
}

function laborator_portfolio_like_column_content( $column, $post_id ) {
	global $post;

	switch ( $column ) {
		case "likes":
			$likes = get_post_likes();
			echo '<span class="likes-num">' . number_format_i18n( $likes['count'], 0 ) . '</span>';
			echo ' <a href="#" data-id="' . $post_id . '" class="portfolio-likes-reset" title="Reset likes for this item"> - <span>Reset</span></a>';
			break;
	}
}

function lab_portfolio_reset_likes_ajax() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
		$post_id = $_POST['post_id'];
		$post    = get_post( $post_id );

		if ( $post && $post->post_type == 'portfolio' ) {
			update_post_meta( $post_id, 'post_likes', array() );

			die( 'success' );
		}
	}
}

add_filter( 'manage_edit-portfolio_columns', 'laborator_portfolio_like_column' );
add_action( 'manage_portfolio_posts_custom_column', 'laborator_portfolio_like_column_content', 10, 2 );
add_action( 'wp_ajax_lab_portfolio_reset_likes', 'lab_portfolio_reset_likes_ajax' );

/**
 * Portfolio Type Column
 */
function kalium_portfolio_item_type_column_filter( $columns ) {

	$columns['item_type'] = 'Item Type';

	if ( isset( $columns['comments'] ) ) {
		unset( $columns['comments'] );
	}

	if ( apply_filters( 'kalium_portfolio_remove_author_column', true ) ) {
		unset( $columns['author'] );
	}

	return $columns;
}

function kalium_portfolio_item_type_column_content_action( $column, $post_id ) {
	global $post;

	if ( $column == 'item_type' ) {

		$item_type = kalium_get_field( 'item_type' );

		$item_types = array(
			'type-1' => 'Side Portfolio',
			'type-2' => 'Columned',
			'type-3' => 'Carousel',
			'type-4' => 'Zig Zag',
			'type-5' => 'Fullscreen',
			'type-6' => 'Lightbox',
			'type-7' => 'WPB Page Builder',
		);

		if ( isset( $item_types[ $item_type ] ) ) :
			?>
            <a href="<?php echo add_query_arg( array( 'portfolio_item_type' => $item_type ) ); // get_edit_post_link( $post );
			?>" class="portfolio-item-type-column">
                <img src="<?php echo kalium()->assets_url( 'admin/images/portfolio/portfolio-item-' . $item_type . '.png' ); ?>"/>
				<?php echo $item_types[ $item_type ]; ?>
            </a>
		<?php
		endif;
	}
}

function kalium_portfolio_item_type_filter_dropdown() {
	global $pagenow, $typenow;

	if ( $pagenow == 'edit.php' && $typenow == 'portfolio' ) {

		$current_item_type = kalium()->request->query( 'portfolio_item_type' );

		$item_types = array(
			'type-1' => 'Side Portfolio',
			'type-2' => 'Columned',
			'type-3' => 'Carousel',
			'type-4' => 'Zig Zag',
			'type-5' => 'Fullscreen',
			'type-6' => 'Lightbox',
			'type-7' => 'WPB Page Builder',
		);
		?>
        <select name="portfolio_item_type" class="postform">
            <option value="">All item types</option>
			<?php
			foreach ( $item_types as $item_type => $name ) :
				?>
                <option <?php echo selected( $current_item_type, $item_type ); ?>
                        value="<?php echo $item_type; ?>"><?php echo $name; ?></option>
			<?php
			endforeach;
			?>
        </select>
		<?php
	}
}

function kalium_portfolio_item_type_filter_query_request( $query ) {
	global $pagenow, $typenow;

	$item_type = kalium()->request->query( 'portfolio_item_type' );

	if ( $pagenow == 'edit.php' && $typenow == 'portfolio' && ! empty( $item_type ) ) {
		$query->query_vars['meta_key']   = 'item_type';
		$query->query_vars['meta_value'] = $item_type;
	}

	return $query;
}

if ( is_admin() ) {
	add_filter( 'manage_edit-portfolio_columns', 'kalium_portfolio_item_type_column_filter' );
	add_action( 'manage_portfolio_posts_custom_column', 'kalium_portfolio_item_type_column_content_action', 10, 2 );
	add_action( 'restrict_manage_posts', 'kalium_portfolio_item_type_filter_dropdown' );
	add_filter( 'parse_query', 'kalium_portfolio_item_type_filter_query_request' );
}

/**
 * Portfolio Listing Lightbox Entries
 */
global $lb_entry_index;
$lb_entry_index = 0;

function kalium_portfolio_get_lightbox_settings_and_items( $items, $gallery_id = 'portfolio-slider' ) {
	kalium_enqueue_lightbox_library();

	$portfolio_lb_speed           = kalium_get_theme_option( 'portfolio_lb_speed' );
	$portfolio_lb_hide_bars_delay = kalium_get_theme_option( 'portfolio_lb_hide_bars_delay' );

	$portfolio_lb_thumbnails_container_height = kalium_get_theme_option( 'portfolio_lb_thumbnails_container_height' );
	$portfolio_lb_thumbnails_width            = kalium_get_theme_option( 'portfolio_lb_thumbnails_width' );

	$portfolio_lb_autoplay_pause = kalium_get_theme_option( 'portfolio_lb_autoplay_pause' );
	$portfolio_lb_zoom           = kalium_get_theme_option( 'portfolio_lb_zoom', '1' );

	$portfolio_lb_zoom_scale = kalium_get_theme_option( 'portfolio_lb_zoom_scale' );

	$start_class = array( 'lg-start-fade' );

	// Lightbox Skin
	$lightbox_skin = kalium_get_theme_option( 'portfolio_lb_skin' );

	switch ( $lightbox_skin ) {

		// Default
		case 'default':
		case 'lg-default-skins': // Deprecated option
			$start_class[] = 'lg-default-skin';
			break;

		// Light
		case 'kalium-light':
		case 'lg-skin-kalium-default lg-skin-kalium-light': // Deprecated option
			$start_class[] = 'lg-kalium lg-kalium-light';
			break;

		// Default - Dark skin
		default:
			$start_class[] = 'lg-kalium lg-kalium-dark';
	}

	$lg_options = array(

		'galleryId'          => $gallery_id,

		// Mode
		'mode'               => kalium_get_theme_option( 'portfolio_lb_mode', 'lg-fade' ),

		// Connected Items
		'singleNavMode'      => kalium_lb_get_navigation_mode() == 'single',

		// Transitions Params
		'speed'              => $portfolio_lb_speed ? floatval( $portfolio_lb_speed * 1000 ) : 600,
		'hideBarsDelay'      => $portfolio_lb_hide_bars_delay ? floatval( $portfolio_lb_hide_bars_delay * 1000 ) : 3000,

		// General Settings
		'hash'               => false,
		'loop'               => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_loop', '1' ) ),
		'kaliumHash'         => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_hash', '1' ) ),
		'download'           => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_download', '1' ) ),
		'counter'            => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_counter', '1' ) ),
		'enableDrag'         => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_draggable', '1' ) ),

		// Pager
		'pager'              => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_pager', '0' ) ),

		// Full Screen
		'fullScreen'         => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_fullscreen', '1' ) ),

		// Thumbnails
		'thumbnail'          => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_thumbnails', '1' ) ),
		'animateThumb'       => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_thumbnails_animated', '1' ) ),
		'pullCaptionUp'      => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_thumbnails_pullcaptions_up', '1' ) ),
		'showThumbByDefault' => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_thumbnails_show', '0' ) ),
		'thumbMargin'        => 0,

		'thumbContHeight' => $portfolio_lb_thumbnails_container_height ? intval( $portfolio_lb_thumbnails_container_height ) : 100,
		'thumbWidth'      => $portfolio_lb_thumbnails_width ? intval( $portfolio_lb_thumbnails_width ) : 100,

		'currentPagerPosition' => 'middle',

		// Auto Play
		'autoplay'             => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_autoplay', '1' ) ),
		'autoplayControls'     => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_autoplay_controls', '1' ) ),
		'fourceAutoplay'       => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_autoplay_force_autoplay', '1' ) ),
		'progressBar'          => wp_validate_boolean( kalium_get_theme_option( 'portfolio_lb_autoplay_progressbar', '1' ) ),

		'pause' => $portfolio_lb_autoplay_pause ? floatval( $portfolio_lb_autoplay_pause * 1000 ) : 5000,

		// Zoom
		'zoom'  => wp_validate_boolean( $portfolio_lb_zoom ),
		'scale' => $portfolio_lb_zoom_scale ? floatval( $portfolio_lb_zoom_scale ) : 1,

		'startClass'     => implode( ' ', $start_class ),

		// Share
		'share'          => false,

		// Rotate
		'rotate'         => kalium_validate_boolean( kalium_get_theme_option( 'portfolio_lb_rotate' ) ),
		'rotateLeft'     => kalium_validate_boolean( kalium_get_theme_option( 'portfolio_lb_rotate_left' ) ),
		'rotateRight'    => kalium_validate_boolean( kalium_get_theme_option( 'portfolio_lb_rotate_right' ) ),
		'flipHorizontal' => kalium_validate_boolean( kalium_get_theme_option( 'portfolio_lb_flip_horizontal' ) ),
		'flipVertical'   => kalium_validate_boolean( kalium_get_theme_option( 'portfolio_lb_flip_vertical' ) ),
	);

	// Share 
	if ( kalium_get_theme_option( 'portfolio_share_item' ) ) {
		$share_networks = kalium_get_theme_option( 'portfolio_share_item_networks' );

		if ( ! empty( $share_networks['visible'] ) ) {
			unset( $share_networks['visible']['placebo'] );

			// Allow sharing
			$lg_options['share'] = true;
		}
	}

	$lg_options = apply_filters( 'kalium_lg_options', $lg_options );

	// Transparent Header Bar
	$transparent_bar = ! $lg_options['download'] && ! $lg_options['counter'] && ! $lg_options['fullScreen'] && ! $lg_options['autoplay'] && ! $lg_options['zoom'];

	if ( $transparent_bar ) {
		$lg_options['startClass'] .= ' transparent-header-bar';
	}

	// Prepare Lightbox Items for JS
	$items_js = array();

	if ( apply_filters( 'kalium_portfolio_cache_lightbox_entries_json', false ) ) {
		$items_transient_key = 'kalium_portfolio_lightbox_items-' . md5( serialize( $items ) );
		$items_cached        = get_transient( $items_transient_key );

		if ( false === $items_cached ) {
			foreach ( $items as $wp_post ) {
				$items_js = array_merge( $items_js, kalium_portfolio_item_lightbox_entry( $wp_post ) );
			}

			set_transient( $items_transient_key, $items_js, DAY_IN_SECONDS * 7 );
		} else {
			$items_js = $items_cached;
		}
	} else {

		foreach ( $items as $wp_post ) {
			$items_js = array_merge( $items_js, kalium_portfolio_item_lightbox_entry( $wp_post ) );
		}
	}

	return array(
		'options' => $lg_options,
		'entries' => $items_js,
	);
}

function kalium_portfolio_item_lightbox_entry( $wp_post ) {

	if ( ! $wp_post instanceof WP_Post ) {
		return array();
	}

	$lb_entries = array();

	$post_id         = $wp_post->ID;
	$content_to_show = $wp_post->content_to_show;
	$video_poster    = $wp_post->video_poster;


	switch ( $content_to_show ) {
		case 'other-image':
			$custom_image = $wp_post->custom_image;

			if ( $custom_image ) {
				$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'other-image', $custom_image );
			} else {
				$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'featured-image' );
			}
			break;

		case 'gallery':
			$gallery = kalium_get_field( 'image_and_video_gallery', $post_id );

			if ( is_array( $gallery ) ) {
				foreach ( $gallery as $i => $item ) {
					$lb_entry = null;

					if ( preg_match( "/image\/.*/i", $item['mime_type'] ) ) { // Image Type
						$lb_entry = kalium_portfolio_lightbox_prepare_item( $wp_post, 'gallery-item-image', $item );
					} elseif ( preg_match( "/video\/.*/i", $item['mime_type'] ) ) { // Video Type
						$lb_entry = kalium_portfolio_lightbox_prepare_item( $wp_post, 'gallery-item-video', $item );
					}

					if ( $lb_entry ) {
						$lb_entry['subIndex'] = $i;
						$lb_entries[]         = $lb_entry;
					}
				}
			}
			break;

		case 'self-hosted-video':
			$self_hosted_video = kalium_get_field( 'self_hosted_video', $post_id );

			if ( preg_match( "/video\/.*/i", $self_hosted_video['mime_type'] ) ) {
				$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'gallery-item-video', $self_hosted_video, array( 'poster' => $video_poster ) );
			}
			break;

		case 'youtube':
			$youtube_video_url = $wp_post->youtube_video_url;

			if ( preg_match( '/youtube\.com/', $youtube_video_url ) ) {
				$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'youtube-video', $youtube_video_url, array( 'poster' => $video_poster ) );
			}
			break;

		case 'vimeo':
			$vimeo_video_url = $wp_post->vimeo_video_url;

			if ( preg_match( '/vimeo\.com/', $vimeo_video_url ) ) {
				$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'vimeo-video', $vimeo_video_url, array( 'poster' => $video_poster ) );
			}
			break;

		default:
			$lb_entries[] = kalium_portfolio_lightbox_prepare_item( $wp_post, 'featured-image' );
	}

	// Enqueue Video library if there is any video
	if ( in_array( $content_to_show, array( 'self-hosted-video', 'youtube', 'vimeo' ) ) ) {
		kalium_enqueue_media_library();
	}

	// Remove empty entries and set sub indexes
	foreach ( $lb_entries as $i => & $lb_entry ) {
		$lb_entry['hash'] = $lb_entry['subIndex'] == 0 ? $lb_entry['slug'] : "{$lb_entry['slug']}/{$lb_entry['subIndex']}";

		if ( empty( $lb_entry['src'] ) && empty( $lb_entry['html'] ) ) {
			unset( $lb_entries[ $i ] );
		}
	}

	return array_filter( $lb_entries );
}

/**
 * Prepare Gallery Item
 */
function kalium_portfolio_lightbox_prepare_item( $wp_post, $item_type, $item = null, $args = array() ) {
	global $post, $lb_entry_index;

	// Lightbox Object
	$lb_entry = array();

	// Get Information
	$post_id           = $wp_post->ID;
	$post_name         = $wp_post->post_name;
	$post_thumbnail_id = get_post_thumbnail_id( $post_id );

	$content_to_show = $wp_post->content_to_show;
	$custom_image    = $wp_post->custom_image;

	// Caption
	$caption_title = '';
	$caption_text  = '';

	// Image Sizes
	$image_size_large = apply_filters( 'kalium_lightbox_image_size_large', 'original' );
	$image_size_thumb = apply_filters( 'kalium_lightbox_image_size_thumbnail', 'thumbnail' );
	$image_size_downl = apply_filters( 'kalium_lightbox_image_size_download', 'original' );

	// Terms/categories of this entry
	$lb_entry['terms']    = wp_get_object_terms( $post_id, 'portfolio_category', array( 'fields' => 'slugs' ) );

	switch ( $item_type ) {

		// Show Custom Image
		case 'other-image' :
			$caption_title = get_the_title( $wp_post );
			$caption_text  = $wp_post->post_content;

			$img_large = wp_get_attachment_image_src( $item, $image_size_large );
			$img_thumb = wp_get_attachment_image_src( $item, $image_size_thumb );

			$img_downl = wp_get_attachment_image_src( $item, $image_size_downl );

			list( $srcset, $sizes ) = kalium_image_get_srcset_and_sizes_from_attachment( $item, $img_large );

			$lb_entry['src']         = $img_large[0];
			$lb_entry['srcset']      = $srcset;
			$lb_entry['sizes']       = $sizes;
			$lb_entry['thumb']       = $img_thumb[0];
			$lb_entry['downloadUrl'] = $img_downl[0];
			break;

		// Gallery Image Item
		case 'gallery-item-image' :
			$caption_title = $item['title'];
			$caption_text  = $item['caption'] ? $item['caption'] : $item['description'];

			$img_large = wp_get_attachment_image_src( $item['id'], $image_size_large );
			$img_thumb = wp_get_attachment_image_src( $item['id'], $image_size_thumb );

			$img_downl = wp_get_attachment_image_src( $item['id'], $image_size_downl );

			list( $srcset, $sizes ) = kalium_image_get_srcset_and_sizes_from_attachment( $item['id'], $img_large );

			$lb_entry['src']         = $img_large[0];
			$lb_entry['srcset']      = $srcset;
			$lb_entry['sizes']       = $sizes;
			$lb_entry['thumb']       = $img_thumb[0];
			$lb_entry['downloadUrl'] = $img_downl[0];

			break;

		// Gallery Video Item
		case 'gallery-item-video' :
			$caption_title = $item['title'];
			$caption_text = $item['caption'] ? $item['caption'] : $item['description'];

			if ( ! empty( $args['poster'] ) ) {
				$img_large = wp_get_attachment_image_src( $args['poster'], $image_size_large );
				$img_thumb = wp_get_attachment_image_src( $args['poster'], $image_size_thumb );

				$lb_entry['poster'] = $img_large[0];
			} else {
				$img_thumb = wp_get_attachment_image_src( $post_thumbnail_id, $image_size_thumb );
			}

			$video_id = 'video-' . md5( $item['id'] . $item['url'] );

			if ( ! empty( $img_large[0] ) ) {
				$lb_entry['poster'] = $img_large[0];
			}

			$lb_entry['thumb'] = $img_thumb[0];
			$lb_entry['html']  = '#' . $video_id;

			ob_start();
			?>
            <div id="<?php echo $video_id; ?>" class="hidden">
                <video class="lg-video-object lg-html5" controls preload="none">
                    <source src="<?php echo $item['url']; ?>" type="<?php echo $item['mime_type']; ?>">
					<?php _e( 'Your browser does not support HTML5 video.', 'kalium' ); ?>
                </video>
            </div>
			<?php
			$video_footer_append = ob_get_clean();

			kalium_append_content_to_footer( $video_footer_append );
			break;


		// YouTube & Vimeo Video
		case 'youtube-video' :
		case 'vimeo-video' :
			$caption_title = get_the_title( $wp_post );
			$caption_text  = $wp_post->post_content;

			if ( ! empty( $args['poster'] ) ) {
				$img_large = wp_get_attachment_image_src( $args['poster'], $image_size_large );
				$img_thumb = wp_get_attachment_image_src( $args['poster'], $image_size_thumb );

				$lb_entry['poster'] = $img_large[0];
			} else {
				$img_thumb = wp_get_attachment_image_src( $post_thumbnail_id, $image_size_thumb );
			}

			$lb_entry['href']  = $item;
			$lb_entry['src']   = $lb_entry['href'];
			$lb_entry['thumb'] = $img_thumb[0];
			break;


		// Show Featured Image
		case 'featured-image' :
			$caption_title = get_the_title( $wp_post );
			$caption_text  = $wp_post->post_content;

			$img_large = wp_get_attachment_image_src( $post_thumbnail_id, $image_size_large );
			$img_thumb = wp_get_attachment_image_src( $post_thumbnail_id, $image_size_thumb );

			$img_downl = wp_get_attachment_image_src( $post_thumbnail_id, $image_size_downl );

			list( $srcset, $sizes ) = kalium_image_get_srcset_and_sizes_from_attachment( $post_thumbnail_id, $img_large );

			$lb_entry['src']         = $img_large[0];
			$lb_entry['srcset']      = $srcset;
			$lb_entry['sizes']       = $sizes;
			$lb_entry['thumb']       = $img_thumb[0];
			$lb_entry['downloadUrl'] = $img_downl[0];
			break;
	}

	// Caption
	if ( kalium_get_theme_option( 'portfolio_lb_captions' ) && ! defined( 'DOING_AJAX' ) ) :

		ob_start();
		?>
        <div class="kalium-portfolio-lightbox-item-caption">

			<?php do_action( 'kalium_portfolio_lightbox_item_before_caption', $post_id ); ?>

			<?php if ( isset( $caption_title ) ) : ?>
                <h4><?php echo esc_html( $caption_title ); ?></h4>
			<?php endif; ?>

			<?php
			if ( isset( $caption_text ) ) :
				echo kalium_format_content( $caption_text );
			endif;
			?>

			<?php do_action( 'kalium_portfolio_lightbox_item_after_caption', $post_id ); ?>

        </div>
		<?php

		$caption_html = ob_get_clean();

		$lb_entry['subHtml'] = $caption_html;

	endif;

	$lb_entry['portfolioItemId'] = $post_id;
	$lb_entry['permalink']       = get_permalink( $wp_post );
	$lb_entry['slug']            = $post_name;
	$lb_entry['index']           = $lb_entry_index ++;
	$lb_entry['subIndex']        = 0;

	// Set href attribute
	if ( ! isset( $lb_entry['href'] ) ) {
		$lb_entry['href'] = get_permalink( $post_id );
	}

	// Social networks share url
	$lb_entry['facebookShareUrl'] = $lb_entry['twitterShareUrl'] = $lb_entry['googleplusShareUrl'] = $lb_entry['pinterestShareUrl'] = $lb_entry['href'];

	return apply_filters( 'kalium_portfolio_lightbox_prepare_item', $lb_entry, $post_id );
}

/**
 * Custom Image Size
 */
$portfolio_lb_image_size_large     = kalium_get_theme_option( 'portfolio_lb_image_size_large' );
$portfolio_lb_image_size_thumbnail = kalium_get_theme_option( 'portfolio_lb_image_size_thumbnail' );

if ( ! empty( $portfolio_lb_image_size_large ) ) {
	add_filter( 'kalium_lightbox_image_size_large', kalium_hook_return_value( $portfolio_lb_image_size_large ), 10 );
}

if ( ! empty( $portfolio_lb_image_size_thumbnail ) ) {
	add_filter( 'kalium_lightbox_image_size_thumbnail', kalium_hook_return_value( $portfolio_lb_image_size_thumbnail ), 10 );
}

/**
 * Get Lightbox Navigation mode
 */
function kalium_lb_get_navigation_mode() {
	if ( in_array( kalium_get_theme_option( 'portfolio_lb_navigation_mode' ), array( '', 'single' ) ) ) {
		return 'single';
	}

	return 'linked';
}

/**
 * Remove Tags Column for Portfolio post type
 */
if ( ! kalium_get_theme_option( 'portfolio_enable_tags' ) ) {
	add_filter( 'portfolioposttype_tag_args', 'portfolioposttype_tag_args_remove_tags_column' );
}

function portfolioposttype_tag_args_remove_tags_column( $args ) {
	$args['show_admin_column'] = false;
	$args['show_ui']           = false;

	return $args;
}

/**
 * Render Portfolio Loop Item
 */
function kalium_portfolio_loop_items_show( $portfolio_args, $return = false ) {
	global $portfolio_args, $i;

	ob_start();

	$i = 0;

	while ( $portfolio_args['portfolio_query']->have_posts() ) : $portfolio_args['portfolio_query']->the_post();

		switch ( $portfolio_args['layout_type'] ) {
			case 'type-1':
				include locate_template( 'tpls/portfolio-loop-item-type-1.php' );
				break;

			case 'type-2':
				include locate_template( 'tpls/portfolio-loop-item-type-2.php' );
				break;
		}

		$i ++;

	endwhile;

	$html = ob_get_clean();

	wp_reset_postdata();

	if ( $return ) {
		return $html;
	} else {
		echo $html;
	}
}

/**
 * Get Portfolio Query Arguments
 *
 * @param array $opts
 *
 * @return mixed|void
 */
function kalium_get_portfolio_query( $opts = [] ) {
	static $portfolio_instance_id = 1;

	// Enqueue Isotope and Packery
	kalium_enqueue_isotope_and_packery_library();

	// Set post ID/path when string or number is given
	if ( is_numeric( $opts ) || is_string( $opts ) ) {
		$opts = [ 'post_id' => $opts ];
	}

	$layout_type  = kalium_get_theme_option( 'portfolio_type' );
	$category_var = kalium_portfolio_get_category_endpoint_var();
	$vc_mode      = false;

	// Get Portfolio Args from Visual Composer Element
	if ( isset( $opts['vc_attributes'] ) ) {
		$vc_mode       = true;
		$vc_attributes = $opts['vc_attributes'];

		// Set Layout Type
		if ( isset( $vc_attributes['portfolio_type'] ) ) {
			$layout_type = $vc_attributes['portfolio_type'];
		}
	}

	$args = [

		// Main Vars
		'layout_type'               => $layout_type,
		'reveal_effect'             => kalium_get_theme_option( 'portfolio_reveal_effect' ),
		'subtitles'                 => kalium_get_theme_option( 'portfolio_loop_subtitles' ),
		'fullwidth'                 => kalium_get_theme_option( 'portfolio_full_width' ),
		'fullwidth_title_container' => kalium_get_theme_option( 'portfolio_full_width_title_filter_container' ),

		// Likes
		'likes'                     => kalium_get_theme_option( 'portfolio_likes' ),
		'likes_icon'                => kalium_get_theme_option( 'portfolio_likes_icon' ),

		// Share
		'share'                     => kalium_get_theme_option( 'portfolio_share_item' ),
		'share_layout'              => kalium_get_theme_option( 'portfolio_like_share_layout' ),
		'share_networks'            => kalium_get_theme_option( 'portfolio_share_item_networks' ),

		// Run as Visual Composer Mode
		'vc_mode'                   => $vc_mode,
		'vc_attributes'             => [],

		// Is Page Mode
		'is_page'                   => false,

		// Portfolio Title Section
		'show_title'                => kalium_get_theme_option( 'portfolio_show_header_title' ),
		'title'                     => kalium_get_theme_option( 'portfolio_title' ),
		'description'               => kalium_get_theme_option( 'portfolio_description' ),

		// Default selected category to filter items
		'filter_category_slug'      => kalium_get_theme_option( 'portfolio_default_filter_category' ),
		'filter_hide_all_link'      => kalium_get_theme_option( 'portfolio_filter_category_hide_all' ),

		// Portfolio Archive URL
		'url'                       => kalium_get_theme_option( 'portfolio_archive_url' ) ? kalium_get_theme_option( 'portfolio_archive_url' ) : get_post_type_archive_link( 'portfolio' ),
		'archive_url_to_category'   => kalium_get_theme_option( 'portfolio_archive_url_category' ),

		// Rewrite
		'rewrite'                   => [
			'portfolio_prefix' => kalium_get_theme_option( 'portfolio_prefix_url_slug' ),
			'category_prefix'  => kalium_get_theme_option( 'portfolio_category_prefix_url_slug' ),
		],

		// Portfolio Layout Types
		'layouts'                   => [

			// Portfolio Layout Type 1
			'type_1' => [
				'dynamic_image_height' => kalium_get_theme_option( 'portfolio_type_1_dynamic_height' ),

				'default_spacing'        => kalium_get_theme_option( 'portfolio_type_1_default_spacing' ),

				// Custom Hover Layer Icon
				'hover_layer_icon'       => kalium_get_theme_option( 'portfolio_type_1_hover_layer_icon' ),
				'hover_layer_icon_image' => kalium_get_theme_option( 'portfolio_type_1_hover_layer_icon_custom' ),
				'hover_layer_icon_width' => kalium_get_theme_option( 'portfolio_type_1_hover_layer_icon_custom_width' ),

				'hover_layer_icon_markup' => '', // Generated below this array declaration

				// Hover
				'hover_effect'            => kalium_get_theme_option( 'portfolio_type_1_hover_effect' ),
				'hover_color'             => kalium_get_theme_option( 'portfolio_type_1_hover_color' ),
				'hover_transparency'      => kalium_get_theme_option( 'portfolio_type_1_hover_transparency' ),
				'hover_style'             => kalium_get_theme_option( 'portfolio_type_1_hover_style' ),
			],

			// Portfolio Layout Type 2
			'type_2' => [
				'dynamic_image_height' => false,
				'show_likes'           => kalium_get_theme_option( 'portfolio_type_2_likes_show' ),

				'grid_spacing'        => kalium_get_theme_option( 'portfolio_type_2_grid_spacing' ),
				'default_spacing'     => kalium_get_theme_option( 'portfolio_type_2_default_spacing' ),

				// Hover
				'hover_effect'        => kalium_get_theme_option( 'portfolio_type_2_hover_effect' ),
				'hover_color'         => kalium_get_theme_option( 'portfolio_type_2_hover_color' ),
				'hover_transparency'  => kalium_get_theme_option( 'portfolio_type_2_hover_transparency' ),
				'hover_style'         => kalium_get_theme_option( 'portfolio_type_2_hover_style' ),
				'hover_text_position' => kalium_get_theme_option( 'portfolio_type_2_hover_text_position' ),
			],
		],

		// Portfolio Single Item Options
		'single'                    => [

			// Single previous-next navigation links
			'prev_next' => [
				// Type and Position
				'type'               => kalium_get_theme_option( 'portfolio_prev_next_type' ),
				'position'           => kalium_get_theme_option( 'portfolio_prev_next_position' ),

				// Include Categories
				'include_categories' => kalium_get_theme_option( 'portfolio_prev_next_category' ) ? true : false,

				// Show prev/next as titles
				'show_titles'        => kalium_get_theme_option( 'portfolio_prev_next_show_titles' ) ? true : false,
			],
		],

		// Columns
		'columns'                   => kalium_get_theme_option( $layout_type == 'type-1' ? 'portfolio_type_1_columns_count' : 'portfolio_type_2_columns_count' ),

		// Posts per Page
		'per_page'                  => kalium_get_theme_option( $layout_type == 'type-1' ? 'portfolio_type_1_items_per_page' : 'portfolio_type_2_items_per_page' ),
		'endless_per_page'          => kalium_get_theme_option( 'portfolio_endless_pagination_fetch_count' ),

		// Pagination
		'pagination'                => [
			'page'    => isset( $opts['paged'] ) ? $opts['paged'] : ( isset( $GLOBALS['paged'] ) ? $GLOBALS['paged'] : 1 ),
			'type'    => kalium_get_theme_option( 'portfolio_pagination_type' ),
			'align'   => kalium_get_theme_option( 'portfolio_pagination_position' ),

			// Endless Pagination Options
			'endless' => [
				// Endless Pagination Style
				'style'              => kalium_get_theme_option( 'portfolio_endless_pagination_style' ),

				// Labels
				'show_more_text'     => __( 'Show More', 'kalium' ),
				'no_more_items_text' => __( 'No more portfolio items to show', 'kalium' ),
			],
		],

		// Set Current Category
		'category'                  => '',

		// Category Filter
		'category_filter'           => kalium_get_theme_option( 'portfolio_category_filter' ),
		'category_filter_subs'      => kalium_get_theme_option( 'portfolio_filter_enable_subcategories' ),
		'category_filter_pushtate'  => kalium_get_theme_option( 'portfolio_filter_link_type' ) == 'pushState',

		// Custom Query
		'custom_query'              => [],

		// Masonry Portfolio
		'masonry_items'             => [],

		// Portfolio Query
		'query'                     => [
			'post_type'   => 'portfolio',
			'post_status' => 'publish',
		],
	];

	// Portfolio Instance ID
	$args['id'] = ( empty( $args['rewrite']['portfolio_prefix'] ) == false ? $args['rewrite']['portfolio_prefix'] : 'portfolio' ) . '-' . $portfolio_instance_id ++;

	// Overwrite instance id
	if ( ! empty( $opts['id'] ) ) {
	    $args['id'] = $opts['id'];
	}

	// Per Page (default value)
	if ( empty( $args['per_page'] ) ) {
		$args['per_page'] = absint( get_option( 'posts_per_page' ) );
	}

	// Override Visual Composer Attributes
	if ( $vc_mode && isset( $vc_attributes ) ) {

		// Set VC Attributes as $args option to pass on AJAX
		$args['vc_attributes'] = $vc_attributes;

		// Portfolio Query
		if ( isset( $vc_attributes['portfolio_query'] ) ) {
			$args['query'] = array_merge( $args['query'], kalium_vc_query_builder( $vc_attributes['portfolio_query'] ) );

			// Posts per Page
			if ( isset( $args['query']['posts_per_page'] ) ) {
				$args['per_page'] = $args['query']['posts_per_page'];
			}
		}

		// Dynamic Image Height
		if ( isset( $vc_attributes['dynamic_image_height'] ) ) {
			$vc_dynamic_image_height                                                                   = $vc_attributes['dynamic_image_height'] == 'yes';
			$args['layouts'][ $layout_type == 'type-1' ? 'type_1' : 'type_2' ]['dynamic_image_height'] = $vc_dynamic_image_height;
		}

		// Columns - Inherit From Theme Options
		if ( isset( $vc_attributes['columns'] ) && $vc_attributes['columns'] != 'inherit' ) {
			$args['columns'] = $vc_attributes['columns'];
		}

		// Item Spacing
		if ( $layout_type == 'type-2' && isset( $vc_attributes['portfolio_spacing'] ) && $vc_attributes['portfolio_spacing'] != 'inherit' ) {
			$args['layouts']['type_2']['grid_spacing'] = $vc_attributes['portfolio_spacing'] == 'yes' ? 'normal' : 'merged';
		}

		// Portfolio Title and Description
		if ( isset( $vc_attributes['title'] ) ) {
			$args['show_title'] = true;
			$args['title']      = $vc_attributes['title'];
		}

		if ( isset( $vc_attributes['description'] ) ) {
			$args['show_title']  = true;
			$args['description'] = $vc_attributes['description'];
		}

		// Default category filter
		$default_filter_category  = $vc_attributes['default_filter_category'];
		$filter_category_hide_all = $vc_attributes['filter_category_hide_all'];

		$args['filter_category_slug'] = $default_filter_category ? $default_filter_category : 'default';
		$args['filter_hide_all_link'] = $default_filter_category && $filter_category_hide_all;


		// Portfolio Filter
		if ( isset( $vc_attributes['category_filter'] ) ) {
			$args['category_filter'] = $vc_attributes['category_filter'] == 'yes';
		}

		// Reveal Effect
		if ( isset( $vc_attributes['reveal_effect'] ) && $vc_attributes['reveal_effect'] != 'inherit' ) {
			$args['reveal_effect'] = $vc_attributes['reveal_effect'];
		}

		// Masonry Items
		if ( isset( $vc_attributes['masonry_items'] ) ) {
			$args['masonry_items']     = $vc_attributes['masonry_items'];
			$args['masonry_items_ids'] = $vc_attributes['masonry_items_ids'];
		}

		// Per Page
		if ( isset( $vc_attributes['per_page'] ) ) {
			$args['per_page'] = $vc_attributes['per_page'];
		}

		// Endless Per Page
		if ( isset( $vc_attributes['endless_per_page'] ) ) {
			$args['endless_per_page'] = $vc_attributes['endless_per_page'];
		}

		// Endless Auto Reveal
		if ( isset( $vc_attributes['endless_auto_reveal'] ) ) {
			$args['pagination']['type'] = $vc_attributes['endless_auto_reveal'] == 'yes' ? 'endless-reveal' : 'endless';
		}

		// Endless Show More Text
		if ( isset( $vc_attributes['endless_show_more_button_text'] ) ) {
			$args['pagination']['endless']['show_more_text'] = $vc_attributes['endless_show_more_button_text'];
		}

		// Endless No More Items Text
		if ( isset( $vc_attributes['endless_no_more_items_button_text'] ) ) {
			$args['pagination']['endless']['no_more_items_text'] = $vc_attributes['endless_no_more_items_button_text'];
		}

		// Full-width Container
		if ( isset( $vc_attributes['portfolio_full_width'] ) && 'inherit' != $vc_attributes['portfolio_full_width'] ) {
			$args['fullwidth'] = $vc_attributes['portfolio_full_width'] == 'yes';

			if ( $args['fullwidth'] && 'inherit' != $vc_attributes['portfolio_full_width_title_container'] ) {
				$args['fullwidth_title_container'] = $vc_attributes['portfolio_full_width_title_container'] == 'yes';
			}
		}
	}

	// Portfolio Item Type 3 – Dynamic Height for Layout Type 2
	if ( $args['layout_type'] == 'type-3' ) {
		$args['layout_type']                               = 'type-2';
		$args['layouts']['type_2']['dynamic_image_height'] = true;
	}

	// Generate Markup for Portfolio Type 1 Thumbnail Icon
	if ( 'type-1' == $args['layout_type'] && 'custom' == $args['layouts']['type_1']['hover_layer_icon'] ) {
		if ( $attachment_id = $args['layouts']['type_1']['hover_layer_icon_image'] ) {
			$attachment_attrs = array();

			if ( is_numeric( $args['layouts']['type_1']['hover_layer_icon_width'] ) ) {
				$attachment_attrs['style'] = "width:{$args['layouts']['type_1']['hover_layer_icon_width']}px";
			}

			$args['layouts']['type_1']['hover_layer_icon_markup'] = wp_get_attachment_image( $attachment_id, 'original', false, $attachment_attrs );
		}
	}

	// Store main description (prevent overwrites from category)
	$args['description_main'] = $args['description'];


	// Get post ID by slug [opts]
	$post_id = isset( $opts['post_id'] ) ? $opts['post_id'] : null;

	if ( is_string( $post_id ) ) {
		$page = get_page_by_path( $post_id );

		if ( $page instanceof WP_Post ) {
			$post_id = $page->ID;
		}
	}

	// Get Portfolio Options from Post Item [opts]
	if ( ! empty( $post_id ) && $vc_mode == false && ( $portfolio = get_post( $post_id ) ) ) {
		$args['is_page'] = true;
		$args['post_id'] = $portfolio->ID;
		$args['url']     = get_permalink( $portfolio );

		// Title
		$args['show_title']  = $portfolio->show_title_description;
		$args['title']       = get_the_title( $portfolio );
		$args['description'] = $portfolio->post_content;

		// Filter Options
		$default_filter_category  = kalium_get_field( 'default_filter_category', $portfolio->ID );
		$filter_category_hide_all = kalium_get_field( 'filter_category_hide_all', $portfolio->ID );

		$args['filter_category_slug'] = $default_filter_category ? $default_filter_category->slug : 'default';
		$args['filter_hide_all_link'] = $default_filter_category && $filter_category_hide_all;

		// Custom Query
		if ( $portfolio->custom_query ) {
			$args['custom_query'] = array(
				'ids'      => $portfolio->portfolio_items,
				'category' => $portfolio->select_from_category,
				'tags'     => $portfolio->select_from_tags,
				'orderby'  => $portfolio->order_by,
				'order'    => $portfolio->order,
			);

			$args['per_page']         = $portfolio->items_per_page;
			$args['endless_per_page'] = $portfolio->endless_per_page;
		}

		// Masonry Portfolio Style
		if ( $portfolio->masonry_style_portfolio ) {
			$args['custom_query']     = array();
			$args['layout_type']      = 'type-2';
			$args['masonry_items']    = kalium_get_field( 'masonry_items_list', $portfolio );
			$args['per_page']         = $portfolio->masonry_items_per_page;
			$args['endless_per_page'] = $portfolio->masonry_endless_per_page;

			if ( ! is_numeric( $args['per_page'] ) ) {
				$args['per_page'] = - 1;
			}

			if ( is_array( $args['masonry_items'] ) ) {
				list( $args['masonry_items'], $args['masonry_items_ids'] ) = kalium_portfolio_masonry_items_order( $args['masonry_items'] );
			} else {
				$args['masonry_items'] = array();
			}
		}

		// Columns Count
		$columns_count = $portfolio->columns_count;

		if ( 'inherit' != $columns_count ) {
			$args['columns'] = $columns_count;
		}

		// Full-width Container
		$full_width      = $portfolio->portfolio_full_width;
		$title_container = $portfolio->portfolio_full_width_title_container;

		if ( 'inherit' != $full_width ) {
			$args['fullwidth'] = $full_width == 'yes';

			// Title Container
			if ( $args['fullwidth'] && 'inherit' != $title_container ) {
				$args['fullwidth_title_container'] = $title_container == 'yes';
			}
		}

		// Layout Type
		switch ( $portfolio->layout_type ) {
			case 'type-1':
				$dynamic_image_height = $portfolio->portfolio_type_1_dynamic_image_height;
				$hover_effect         = $portfolio->portfolio_type_1_thumbnail_hover_effect;
				$hover_color          = $portfolio->portfolio_type_1_custom_hover_background_color;

				$args['layout_type'] = $portfolio->layout_type;

				if ( 'inherit' != $dynamic_image_height ) {
					$args['layouts']['type_1']['dynamic_image_height'] = $dynamic_image_height == 'yes';
				}

				if ( 'inherit' != $hover_effect ) {
					$args['layouts']['type_1']['hover_effect'] = $hover_effect;
				}

				if ( 'inherit' != $hover_color ) {
					$args['layouts']['type_1']['hover_color'] = $hover_color;
				}
				break;

			case 'type-2':
				$dynamic_image_height = $portfolio->portfolio_type_2_dynamic_image_height;
				$default_spacing      = $portfolio->portfolio_type_2_grid_spacing; // referred to `default_spacing` (it was my mistake)
				$hover_effect         = $portfolio->portfolio_type_2_thumbnail_hover_effect;
				$hover_style          = $portfolio->portfolio_type_2_thumbnail_hover_style;
				$hover_color          = $portfolio->portfolio_type_2_custom_hover_background_color;
				$hover_text_position  = $portfolio->portfolio_type_2_thumbnail_hover_text_position;

				$args['layout_type'] = $portfolio->layout_type;

				if ( ! empty( $dynamic_image_height ) && $dynamic_image_height != 'inherit' ) {
					$args['layouts']['type_2']['dynamic_image_height'] = $dynamic_image_height == 'yes';
				}

				if ( ! empty( $default_spacing ) && 'inherit' != $default_spacing ) {
					$args['layouts']['type_2']['grid_spacing'] = $default_spacing;
				}

				if ( ! empty( $hover_effect ) && 'inherit' != $hover_effect ) {
					$args['layouts']['type_2']['hover_effect'] = $hover_effect;
				}

				if ( ! empty( $hover_color ) && 'inherit' != $hover_color ) {
					$args['layouts']['type_2']['hover_color'] = $hover_color;
				}

				if ( ! empty( $hover_style ) && 'inherit' != $hover_style ) {
					$args['layouts']['type_2']['hover_style'] = $hover_style;
				}

				if ( ! empty( $hover_text_position ) && 'inherit' != $hover_text_position ) {
					$args['layouts']['type_2']['hover_text_position'] = $hover_text_position;
				}
				break;
		}

		// Reveal Effect
		$reveal_effect = $portfolio->reveal_effect;

		if ( $reveal_effect != 'inherit' ) {
			$args['reveal_effect'] = $reveal_effect;
		}
	}

	// Get from base Category slug
	if ( $get_from_category = get_query_var( 'portfolio_category' ) ) {
		$args['category'] = $get_from_category;
	} // Get from Category [query_vars]
    elseif ( $get_from_category = get_query_var( $category_var ) ) {
		$args['category'] = $get_from_category;
	} // Get from Category [opts]
    elseif ( isset( $opts['category'] ) ) {
		$args['category'] = $opts['category'];
	}

	// Translate Columns Number
	$args['columns'] = kalium_portfolio_columns_translate_to_number( $args['columns'] );

	/* Portfolio Query Arguments */
	if ( empty( $opts['no_query'] ) ) {
		$query      = $args['query'];
		$tax_query  = array();
		$meta_query = array();

		// Populate Query when Portfolio Archive is active
		if ( isset( $GLOBALS['wp_query']->is_archive ) && ! empty( $GLOBALS['wp_query']->query ) ) {
			$query = array_merge( $query, $GLOBALS['wp_query']->query );
		}

		// Remove not allowed attributes in this query attribute
		foreach ( array( 'p', 'pagename', 'page_id', 'name', 'portfolio', 'preview' ) as $query_arg ) {
			if ( isset( $query[ $query_arg ] ) ) {
				unset( $query[ $query_arg ] );
			}
		}

		// Custom Query
		if ( ! empty( $args['custom_query'] ) ) {

			// Select post IDS
			if ( isset( $args['custom_query']['ids'] ) && ! empty( $args['custom_query']['ids'] ) ) {
				$query['post__in'] = $args['custom_query']['ids'];
			}

			// Select Category/Categories
			if ( isset( $args['custom_query']['category'] ) && is_array( $args['custom_query']['category'] ) ) {
				$tax_query = array_merge( $tax_query, array(
					'relation' => 'OR',
					array(
						'taxonomy'         => 'portfolio_category',
						'field'            => 'id',
						'terms'            => $args['custom_query']['category'],
						'include_children' => false,
					),
				) );

				$query['post__in'] = array(); // Ignore selected IDs in this case
			}

			// Select Tag/Tags
			if ( isset( $args['custom_query']['tags'] ) && is_array( $args['custom_query']['tags'] ) ) {
				$tax_query = array_merge( $tax_query, array(
					'relation' => 'OR',
					array(
						'taxonomy'         => 'portfolio_tag',
						'field'            => 'id',
						'terms'            => $args['custom_query']['tags'],
						'include_children' => false,
					),
				) );

				$query['post__in'] = array(); // Ignore selected IDs in this case
			}

			// Order by
			if ( isset( $args['custom_query']['orderby'] ) ) {
				$query['orderby'] = $args['custom_query']['orderby'];
			}

			// Order type
			if ( isset( $args['custom_query']['order'] ) ) {
				$query['order'] = $args['custom_query']['order'];
			}
		}

		// Masonry Items
		if ( isset( $args['masonry_items_ids'] ) ) {
			$query['post__in'] = $args['masonry_items_ids'];
			$query['orderby']  = 'post__in';
			$query['order']    = 'ASC';
		}

		// Pagination
		if ( $args['per_page'] ) {
			$query['posts_per_page'] = $args['per_page'];
		}

		if ( is_numeric( $args['pagination']['page'] ) ) {
			$query['paged'] = $args['pagination']['page'];
		}

		// Show only items with featured image
		$meta_query[] = array(
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		);

		// Query Args Extend from Options [opts]
		if ( isset( $opts['query_args'] ) && is_array( $opts['query_args'] ) ) {
			$query = array_merge( $query, $opts['query_args'] );
		}

		// Move "portfolio_tag" to tax queries
		if ( isset( $query['portfolio_tag'] ) ) {
			$get_tag = $query['portfolio_tag'];

			$tax_query = array_merge( $tax_query, array(
				'relation' => 'AND',
				array(
					'taxonomy'         => 'portfolio_tag',
					'field'            => is_string( $get_tag ) ? 'slug' : 'id',
					'terms'            => is_array( $get_tag ) ? $get_tag : array( $get_tag ),
					'include_children' => false,
				),
			) );

			unset( $query['portfolio_tag'] );
		}

		// Get from Category
		$get_category = $args['category'];

		if ( $get_category ) {
			if ( empty( $tax_query ) ) {
				$query['portfolio_category'] = $get_category;
			} else {
				// Continue adding tax queries on the current tax array
				$tax_query = array_merge( $tax_query, array(
					'relation' => 'AND',
					array(
						'taxonomy'         => 'portfolio_category',
						'field'            => is_string( $get_category ) ? 'slug' : 'id',
						'terms'            => is_array( $get_category ) ? $get_category : array( $get_category ),
						'include_children' => false,
					),
				) );
			}
		}


		// Assign Tax query	
		if ( ! empty( $tax_query ) ) {
			$query['tax_query'] = $tax_query;
		}

		// Assign Meta Query
		$query['meta_query'] = $meta_query;

		// Array diff between Ignore and Include		
		if ( isset( $query['post__in'] ) && isset( $query['post__not_in'] ) ) {
			$query['post__in'] = array_diff( $query['post__in'], $query['post__not_in'] );
		}

		// Assign Query
		$args['query'] = apply_filters( 'kalium_portfolio_pre_query', $query );


		// Disable Post Types Order
		$orderby_field                     = isset( $query['orderby'] ) ? strtolower( $query['orderby'] ) : '';
		$orderby_fields_disable_order      = array( 'post__in', 'rand', 'date' );
		$orderby_fields_disable_order_true = $orderby_field && in_array( $orderby_field, $orderby_fields_disable_order );

		if ( $orderby_fields_disable_order_true ) {
			kalium_portfolio_toggle_post_type_ordering( 'disable' );
		}

		// When using post__not_in, alter LIMIT declaration in SQL
		$post__not_in_present = isset( $query['post__not_in'] ) && ! empty( $query['post__not_in'] ) && $query['posts_per_page'] > 0;

		if ( $post__not_in_present ) {
			$post_limits_fn = kalium_hook_return_value( sprintf( 'LIMIT %d', $query['posts_per_page'] ) );
			add_filter( 'post_limits', $post_limits_fn, 10, 2 );
		}

		// Get Available Terms
		$available_terms_query_args = array_merge( $args['query'], array(
			'portfolio_category' => '',
			'portfolio_tag'      => '',
		) );

		$args['available_terms'] = laborator_get_available_terms_for_query( $available_terms_query_args, 'portfolio_category' );

		// Execute Query
		$query = new WP_Query( apply_filters( 'kalium_portfolio_query', $args['query'] ) );

		// Enable Post Types Order
		if ( $orderby_fields_disable_order_true ) {
			kalium_portfolio_toggle_post_type_ordering( 'enable' );
		}

		// Remove posts_limit filter if is set
		if ( $post__not_in_present ) {
			remove_filter( 'post_limits', $post_limits_fn, 10 );
		}

		// Get Lightbox Settings for Portfolio Items that have that type
		$portfolio_lightbox_query_args                     = $available_terms_query_args;
		$portfolio_lightbox_query_args['posts_per_page']   = - 1;
		$portfolio_lightbox_query_args['suppress_filters'] = false; // WPML fix to prevent other languages to include
		$portfolio_lightbox_query_args['meta_query'][]     = array(
			'key'      => 'item_type',
			'operator' => '=',
			'value'    => 'type-6',
		);

		$args['lightbox_items'] = null;

		if ( ! defined( 'DOING_AJAX' ) && ( $portfolio_lightbox_query = get_posts( $portfolio_lightbox_query_args ) ) ) {
			$args['lightbox_items'] = kalium_portfolio_get_lightbox_settings_and_items( $portfolio_lightbox_query, $args['id'] );
		}

		// Assign $query to $args as portfolio_query
		$args['portfolio_query'] = $query;

		// Pagination Info
		$args['pagination']['paged']         = isset( $query->query_vars['paged'] ) ? $query->query_vars['paged'] : 1;
		$args['pagination']['max_num_pages'] = $query->max_num_pages;
		$args['pagination']['found_posts']   = $query->found_posts;

		// Apply Custom Syling
		if ( ! defined( 'DOING_AJAX' ) ) {
			$portfolio_instance_id_attr = "#{$args['id']}";

			// Spacing of Hover Backrounds
			if ( isset( $args['masonry_items_ids'] ) ) {
				$spacing           = ( ! empty( $args['layouts']['type_2']['default_spacing'] ) ? $args['layouts']['type_2']['default_spacing'] : 30 ) / 2;
				$thumbnail_spacing = $spacing;

				// Remove spacing when merged layout is applied
				if ( $args['layouts']['type_2']['grid_spacing'] == 'merged' ) {
					$spacing = 0;
				}

				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder .thumb .hover-state.hover-full', "margin: {$spacing}px;" );

				// Merged Spacing Items
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder.merged-item-spacing .thumb .hover-state.hover-full', "margin: 0px;" );
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder.merged-item-spacing .thumb .hover-state.hover-distanced', "left: {$thumbnail_spacing}px; right: {$thumbnail_spacing}px; top: {$thumbnail_spacing}px; bottom: {$thumbnail_spacing}px;" );

				// Spacing for distanced hover background
				$spacing += 15;
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder .thumb .hover-state.hover-distanced', "left: {$thumbnail_spacing}px; right: {$thumbnail_spacing}px; top: {$thumbnail_spacing}px; bottom: {$thumbnail_spacing}px;" );
			}

			// Hover - Custom CSS
			if ( $args['layouts']['type_1']['hover_color'] ) {
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder .item-box .photo .on-hover', "background-color: {$args['layouts']['type_1']['hover_color']} !important;" );
			}

			if ( $hover_color = $args['layouts']['type_2']['hover_color'] ) {
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder .item-box .thumb .hover-state', "background-color: {$hover_color} !important;" );
				kalium_append_custom_css( $portfolio_instance_id_attr . '.portfolio-holder .item-box .thumb .hover-state .likes .like-btn > *', "color: {$hover_color} !important;" );
			}

			// Default spacing for 
			if ( 'type-1' == $args['layout_type'] && is_numeric( $args['layouts']['type_1']['default_spacing'] ) ) {
				$spacing           = $args['layouts']['type_1']['default_spacing'] / 2;
				$container_spacing = 15 - $spacing;
				kalium_append_custom_css( '.portfolio-holder', "margin-left: {$container_spacing}px; margin-right: {$container_spacing}px;" );
				kalium_append_custom_css( '.portfolio-holder .portfolio-item.has-padding', "padding: {$spacing}px;" );
			}
		}
	}

	return apply_filters( 'kalium_get_portfolio_query', $args );
}

/**
 * Prepare item ids for Masonry Portfolio Style
 */
function kalium_portfolio_masonry_items_order( $items ) {
	$items_arr = array();
	$item_ids  = array();

	if ( is_array( $items ) && ! empty( $items ) ) {
		foreach ( $items as $items_row ) {
			foreach ( $items_row['items_row'] as $item ) {
				if ( $item['item'] instanceof WP_Post ) {
					$items_arr[ $item['item']->ID ] = $item;
					$item_ids[]                     = $item['item']->ID;
				}
			}
		}
	}

	return array( $items_arr, $item_ids );
}

/**
 * Enable or Disable Post Types ordering plugin filters
 */
function kalium_portfolio_toggle_post_type_ordering( $enable = false ) {
	global $CPTO;

	if ( function_exists( 'CPTOrderPosts' ) || isset( $CPTO ) ) {
		if ( $enable === true || $enable == 'enable' ) {
			// Revert back post ordering filter
			if ( isset( $CPTO ) ) {
				add_filter( 'posts_orderby', array( $CPTO, 'posts_orderby' ), 99, 2 );
			} else {
				add_filter( 'posts_orderby', 'CPTOrderPosts', 99, 2 );
			}
		} else {
			// Remove post type ordering
			if ( isset( $CPTO ) ) {
				remove_filter( 'posts_orderby', array( $CPTO, 'posts_orderby' ), 99 );
			} else {
				remove_filter( 'posts_orderby', 'CPTOrderPosts', 99 );
			}
		}
	}
}

/**
 * Portfolio Category Endpoint Var
 */
function kalium_portfolio_get_category_endpoint_var() {
	$category_prefix = kalium_get_theme_option( 'portfolio_category_prefix_url_slug' );
	$category_var    = $category_prefix ? $category_prefix : 'portfolio-category';

	return $category_var;
}

/**
 * Portfolio Category Endpoint
 */
function kalium_portfolio_category_endpoint_action() {
	add_rewrite_endpoint( kalium_portfolio_get_category_endpoint_var(), EP_ALL );
}

add_action( 'init', 'kalium_portfolio_category_endpoint_action' );

/**
 * Portfolio Get Category Permalink
 */
function kalium_portfolio_get_category_link( $term ) {
	global $portfolio_args, $wp_rewrite;

	$category_permastruct = ltrim( apply_filters( 'pre_term_link', $wp_rewrite->get_extra_permastruct( 'portfolio_category' ), $term ), '/' );

	if ( $category_permastruct && $term instanceof WP_Term ) {
		if ( $portfolio_args['is_page'] || $portfolio_args['vc_mode'] ) {
			$category_permastruct = '/' . str_replace( '%portfolio_category%', $term->slug, $category_permastruct );
			$term_link            = rtrim( get_permalink( get_queried_object_id() ), '/' ) . rtrim( $category_permastruct, '/' ) . '/';

			return $term_link;
		}
	}

	return get_term_link( $term, 'portfolio_category' );
}

/**
 * Portfolio Columns Translate to Number (Deprecated Column Values)
 */
function kalium_portfolio_columns_translate_to_number( $cols ) {

	if ( is_string( $cols ) ) {
		switch ( $cols ) {
			// Four items per row
			case 'three':
			case '':
				$cols = 4;
				break;

			// Three items per row
			case 'four':
				$cols = 3;
				break;

			// Two items per row
			case 'six':
				$cols = 2;
				break;

			// Items per row
			case '1 Item per Row':
				$cols = 1;
				break;
			case '2 Items per Row':
				$cols = 2;
				break;
			case '3 Items per Row':
				$cols = 3;
				break;
			case '4 Items per Row':
				$cols = 4;
				break;
			case '5 Items per Row':
				$cols = 5;
				break;
			case '6 Items per Row':
				$cols = 6;
				break;
		}
	}

	return $cols;
}

/**
 * Portfolio CSS Column Class Based on Number
 */
function kalium_portfolio_get_columns_class( $cols ) {
	$css_class = 'w3';

	switch ( $cols ) {
		// One Column
		case 1:
			$css_class = 'w12';
			break;

		// Three Columns
		case 2:
			$css_class = 'w6';
			break;

		// Three Columns
		case 3:
			$css_class = 'w4';
			break;

		// Four Columns
		case 4:
			$css_class = 'w3';
			break;

		// Five Columns
		case 5:
			$css_class = 'w2-4';
			break;

		// Six Columns
		case 6:
			$css_class = 'w2';
			break;
	}

	return $css_class;
}

/**
 * Portfolio Instance Object (JavaScript Declaration)
 */
function kalium_portfolio_generate_portfolio_instance_object( $portfolio_args ) {

	// Post ID
	$post_id = isset( $portfolio_args['post_id'] ) ? $portfolio_args['post_id'] : 0;

	// VC Attributes
	$vc_attributes = $portfolio_args['vc_attributes'];

	// Custom number of items to fetch
	$endless_per_page = is_numeric( $portfolio_args['endless_per_page'] ) ? $portfolio_args['endless_per_page'] : 0;

	// Lightbox Data
	$lightbox_items = $portfolio_args['lightbox_items'];

	// Generate Portfolio Alias
	$portfolio_alias = $portfolio_args['id'];

	if ( ! empty( $portfolio_args['title'] ) ) {
		$portfolio_alias = sanitize_title( $portfolio_args['title'] );
	}

	// Query
	$query      = $portfolio_args['portfolio_query'];
	$base_query = $query->query;

	// WPML translate object IDs
	if ( kalium()->is->wpml_active() && ! empty( $base_query['post__in'] ) ) {
		foreach ( $base_query['post__in'] as & $id ) {
			$id = apply_filters( 'wpml_object_id', $id, 'portfolio', true );
		}
	}

	// Endless Per Page
	if ( $endless_per_page != 0 ) {
		$base_query['posts_per_page'] = $endless_per_page;
	}

	// Category Counter
	$category_post_count = array();

	$category_query = array_merge( $base_query, array(
		'paged'              => 0,
		'posts_per_page'     => - 1,
		'portfolio_category' => '',
		'fields'             => 'ids',
	) );

	// Count All Items (All Categories)
	$all_items_count = count( get_posts( $category_query ) );

	// Tax Query Array of Current Query
	$category_tax_query = isset( $category_query['tax_query'] ) ? $category_query['tax_query'] : array();

	foreach ( $portfolio_args['available_terms'] as $term ) {

		$category_query['tax_query'] = array_merge( $category_tax_query, array(
			'relation' => 'AND',
			array(
				'taxonomy'         => 'portfolio_category',
				'field'            => 'id',
				'terms'            => $term->term_id,
				'include_children' => false,
			),
		) );

		$category_post_count[ $term->slug ] = count( get_posts( $category_query ) );
	}

	// Portfolio Instance Object used for Pagination
	$portfolio_container_data = array(
		'instanceId'    => $portfolio_args['id'],
		'instanceAlias' => $portfolio_alias,

		'baseQuery'    => $base_query,
		'vcAttributes' => $vc_attributes,

		'postId' => $post_id,

		'count'        => $all_items_count,
		'countByTerms' => $category_post_count,

		'lightboxData' => $lightbox_items,

		'filterPushState' => $portfolio_args['category_filter_pushtate'],
	);
	?>
    <script type="text/javascript">
		var portfolioContainers = portfolioContainers || [];
		portfolioContainers.push( <?php echo json_encode( $portfolio_container_data ); ?> );
    </script>
	<?php
}

/**
 * Portfolio Endless Pagination Button
 */
function kalium_portfolio_endless_pagination( $portfolio_args ) {

	// Loader Type (Icon)
	switch ( $portfolio_args['pagination']['endless']['style'] ) {
		case '_2':
			$loader_type = 2;
			break;

		default:
		case '_1':
			$loader_type = 1;
	}


	?>
    <div class="portfolio-endless-pagination endless-pagination endless-pagination-alignment-<?php echo $portfolio_args['pagination']['align'];
	echo when_match( $portfolio_args['pagination']['max_num_pages'] <= 1, 'not-visible' ); ?>">
        <div class="show-more<?php echo " type-{$loader_type}";
		echo when_match( $portfolio_args['pagination']['type'] == 'endless-reveal', 'auto-reveal' );
		echo when_match( $portfolio_args['pagination']['max_num_pages'] <= 1, 'is-finished' ); ?>" data-endless="true">
            <div class="reveal-button">
                <a href="#" class="btn btn-white">
					<?php echo $portfolio_args['pagination']['endless']['show_more_text']; ?>

                    <span class="loading">
					<?php
					if ( 2 == $loader_type ) {
						echo '<i class="loading-spinner-1"></i>';
					} else {
						echo '<i class="fa fa-circle-o-notch fa-spin"></i>';
					}
					?>
					</span>

                    <span class="finished">
						<?php echo $portfolio_args['pagination']['endless']['no_more_items_text']; ?>
					</span>
                </a>
            </div>
        </div>
    </div>
	<?php
}

/**
 * Portfolio Endless Pagination with AJAX
 */
function portfolio_items_get_from_ajax() {
	global $portfolio_args;

	// Execute actions before processing portfolio ajax query
	do_action( 'portfolio_items_get_from_ajax_before' );

	// Response
	$resp = array();

	// Get Vars
	$instance_id   = kalium()->request->input( 'instanceId' );
	$base_query    = kalium()->request->input( 'baseQuery' );
	$vc_attributes = kalium()->request->input( 'vcAttributes' );
	$post_id       = kalium()->request->input( 'postId' );
	$shown_ids     = kalium()->request->input( 'shownIds' );
	$count         = kalium()->request->input( 'count' );
	$count_terms   = kalium()->request->input( 'countByTerms' );

	// Show all items, ignore category var
	$no_category = kalium()->request->input( 'noCategory' );

	// Get Single Category of Items
	$get_category = kalium()->request->input( 'portfolioCategory' );

	// Make Portfolio Query 
	$query = array_merge( $base_query, [
		'ignore_sticky_posts' => true,
		'post__not_in'        => $shown_ids,
		'paged'               => 0,
	] );

	// Ignore "portfolio_category" query var
	if ( $no_category ) {
		$query['portfolio_category'] = '';
	}

	// Portfolio Query Args
	$portfolio_query_args = array(
	    'id' => $instance_id,
		'query_args' => $query,
	);

	// VC Attributes
	if ( is_array( $vc_attributes ) && count( $vc_attributes ) ) {
		$portfolio_query_args['vc_attributes'] = $vc_attributes;
	}

	// Inherhit Options from specific "Portfolio Page" template
	if ( is_numeric( $post_id ) && $post_id != 0 ) {
		$portfolio_query_args['post_id'] = $post_id;
	}

	// Browse Specific Category
	if ( $get_category ) {
		$portfolio_query_args['category'] = $get_category;
	}

	// Execute Query
	$portfolio_args = kalium_get_portfolio_query( $portfolio_query_args );

	// Render Portfolio Templates
	$resp['html'] = kalium_portfolio_loop_items_show( $portfolio_args, true );

	// Query Meta
	$portfolio_query = $portfolio_args['portfolio_query'];
	$post_count      = $portfolio_query->post_count;
	$shown_items     = count( $shown_ids ) + $post_count;

	$has_more = $shown_items < $count;

	// When browsing single category, $has_more calculates differently
	if ( $get_category ) {
		$get_category_q = array_merge( $query, array(
			'fields'         => 'ids',
			'posts_per_page' => - 1,
		) );

		$get_category_ids = array_intersect( $shown_ids, get_posts( $get_category_q ) );
		$shown_items      = count( $get_category_ids ) + $post_count;
		$has_more         = $shown_items < $portfolio_query->found_posts;
	}

	// Tell if there are more items left
	$resp['hasMore'] = $has_more;

	// Parse JSON Parameters Object
	die( json_encode( $resp ) );
}

add_action( 'wp_ajax_portfolio_items_get_from_ajax', 'portfolio_items_get_from_ajax' );
add_action( 'wp_ajax_nopriv_portfolio_items_get_from_ajax', 'portfolio_items_get_from_ajax' );

/**
 * Show Subcategories for the current Term
 */
function kalium_portfolio_get_terms_by_parent_id( $parent_term, $args ) {

	extract( $args ); // $available_terms, $current_category

	$sub_terms = array();

	if ( empty( $available_terms ) || ! is_array( $available_terms ) ) {
		return;
	}

	foreach ( $available_terms as $term ) {
		if ( $term->parent == $parent_term->term_id ) {
			$sub_terms[] = $term;
		}
	}

	if ( ! count( $sub_terms ) ) {
		return;
	}

	// Go Back Link (Parent Category)
	$go_back_link = kalium_portfolio_get_category_link( $parent_term );

	// Term description
	$term_description = nl2br( trim( term_description( $parent_term ) ) );

	?>
    <ul class="portfolio-subcategory<?php echo when_match( isset( $parent_term->is_active ), 'is-active' ); ?>"
        data-sub-category-of="<?php echo esc_attr( $parent_term->slug ); ?>">
        <li class="subcategory-back">
            <a href="<?php echo esc_url( $go_back_link ); ?>" class="subcategory-back-href"
               data-term="<?php echo esc_attr( $parent_term->slug ); ?>"
				<?php if ( ! empty( $term_descriptions ) ) : ?> data-term-description="<?php echo esc_attr( $term_description ); ?>"<?php endif; ?>>
                <i class="fa fa-angle-left"></i>
                <span><?php echo sprintf( _x( '%s:', 'current portfolio subcategory', 'kalium' ), $parent_term->name ); ?></span>
            </a>
        </li>
		<?php
		foreach ( $sub_terms as $term ) :
			$is_active = $current_category && $current_category == $term->slug;
			$term_link = kalium_portfolio_get_category_link( $term );
			$term_description = nl2br( trim( term_description( $term ) ) );
			?>
            <li class="portfolio-category-item portfolio-category-<?php echo $term->slug;
			when_match( $is_active, 'active' ); ?>">
                <a href="<?php echo esc_url( $term_link ); ?>"
                   data-term="<?php echo esc_attr( $term->slug ); ?>"
					<?php if ( ! empty( $term_descriptions ) ) : ?> data-term-description="<?php echo esc_attr( $term_description ); ?>"<?php endif; ?>><?php echo esc_html( $term->name ); ?></a>
            </li>
		<?php
		endforeach;
		?>
    </ul>
	<?php
}

/**
 * Set Active Term Parents Based on Current Active Term
 */
function kalium_portfolio_set_active_term_parents( $current_term, &$available_terms ) {
	foreach ( $available_terms as & $term ) {
		if ( $current_term->parent == $term->term_id ) {
			$term->is_active = true;

			return true;
		}
	}

	return false;
}

/**
 * Check if Given Term has Sub Terms
 */
function kalium_portfolio_check_if_term_has_children( $current_term, &$available_terms ) {
	foreach ( $available_terms as & $term ) {
		if ( $current_term->term_id == $term->parent ) {
			$term->is_active = true;

			return true;
		}
	}

	return false;
}

/**
 * Portfolio Gallery Gap
 */
function kalium_portfolio_generate_gallery_gap( $gap = 30, $type = '' ) {
	$gap_half = $gap / 2;

	if ( 'carousel' === $type ) {
		kalium_append_custom_css( '.single-portfolio-holder.portfolio-type-3 .gallery-slider .gallery-item', "margin-left:{$gap_half}px; margin-right:{$gap_half}px;" );
	} else {
		$media_spacing = apply_filters( 'kalium_portfolio_generate_gallery_gap_media', 'screen and (min-width: 768px)' );

		kalium_append_custom_css( '.single-portfolio-holder .gallery .photo, .single-portfolio-holder .gallery .portfolio-video, .single-portfolio-holder .gallery .portfolio-images-slider, .single-portfolio-holder .gallery .comparison-image-slider', "margin-bottom:{$gap}px;", $media_spacing ); //@ToDo
		kalium_append_custom_css( '.single-portfolio-holder .gallery .row', "margin-left:-{$gap_half}px; margin-right:-{$gap_half}px;", $media_spacing );
		kalium_append_custom_css( '.single-portfolio-holder .gallery .row > div', "padding-left:{$gap_half}px; padding-right:{$gap_half}px;", $media_spacing );
	}
}

/**
 * Portfolio prefix remove
 */
function laborator_remove_portfolio_prefix_from_link( $post_link, $post, $leavename ) {

	if ( 'portfolio' != $post->post_type || 'publish' != $post->post_status ) {
		return $post_link;
	}

	$custom_prefix = kalium_get_theme_option( 'portfolio_prefix_url_slug' );
	$prefix        = $custom_prefix ? $custom_prefix : $post->post_type;

	$post_link = str_replace( '/' . $prefix . '/', '/', $post_link );

	return $post_link;
}

function laborator_get_portfolio_item_without_post_archive_prefix( $query ) {

	// Only noop the main query
	if ( ! $query->is_main_query() || is_404() ) {
		return;
	}

	// Only noop our very specific rewrite rule match
	if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
		return;
	}

	// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
	if ( ! empty( $query->query['name'] ) ) {
		$query->set( 'post_type', [ 'post', 'portfolio', 'page' ] );
	}
}

if ( ! kalium_validate_boolean( kalium_get_theme_option( 'portfolio_url_add_prefix' ) ) ) {
	add_filter( 'post_type_link', 'laborator_remove_portfolio_prefix_from_link', 10, 3 );
	add_action( 'pre_get_posts', 'laborator_get_portfolio_item_without_post_archive_prefix' );
}
