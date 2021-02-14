<?php
/**
 * Kalium WordPress Theme
 *
 * Other/uncategorized functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Null function.
 *
 * @return void
 */
function kalium_null_function() {
}

/**
 * Conditional return value.
 *
 * @param bool  $condition
 * @param mixed $if
 * @param mixed $else
 *
 * @return mixed
 */
function kalium_conditional( $condition, $if, $else = null ) {
	return $condition ? $if : $else;
}

/**
 * Enqueue GSAP library.
 *
 * @return void
 */
function kalium_enqueue_gsap_library() {
	kalium_enqueue( 'gsap' );
}

/**
 * Enqueue Isotope & Packery library.
 *
 * @return void
 */
function kalium_enqueue_isotope_and_packery_library() {
	kalium_enqueue( 'isotope' );

	// Workaround for WPBakery
	if ( wp_script_is( 'isotope', 'registered' ) ) {
		wp_dequeue_script( 'isotope' );
		wp_deregister_script( 'isotope' );
	}
}

/**
 * Enqueue media library.
 *
 * @return void
 */
function kalium_enqueue_media_library() {
	kalium()->media->enqueue_media_library();
}

/**
 * Enqueue flickity carousel library.
 *
 * @return void
 */
function kalium_enqueue_flickity_library() {
	kalium_enqueue( 'flickity' );
}

/**
 * Enqueue flickity fade library.
 *
 * @return void
 */
function kalium_enqueue_flickity_fade_library() {
	kalium_enqueue( 'flickity-fade' );
}

/**
 * Enqueue Slick Gallery.
 *
 * @return void
 */
function kalium_enqueue_slick_slider_library() {
	kalium_enqueue( 'slick' );
}

/**
 * Enqueue Lightbox Gallery.
 *
 * @return void
 */
function kalium_enqueue_lightbox_library() {
	kalium_enqueue( 'light-gallery' );
}

/**
 * Enqueue ScrollMagic library.
 *
 * @return void
 */
function kalium_enqueue_scrollmagic_library() {
	kalium_enqueue( 'scrollmagic' );

	if ( defined( 'KALIUM_DEBUG' ) ) {
		kalium_enqueue( 'scrollmagic-debug-js' );
	}
}

/**
 * Enqueue Kalium Sticky Header.
 *
 * @return void
 */
function kalium_enqueue_sticky_header() {
	kalium_enqueue( 'sticky-header-js' );
}

/**
 * Is holiday season (13 dec – 05 jan).
 *
 * @return bool
 */
function kalium_is_holiday_season() {
	$current_year = (int) date( 'Y' );
	$date_start   = sprintf( '%d-12-13', $current_year );
	$date_end     = sprintf( '%d-01-04', $current_year + 1 );

	return strtotime( $date_start ) <= time() && strtotime( $date_end ) >= time();
}

/**
 * Register dynamic translatable string for WPML.
 *
 * @param string $name
 * @param string $value
 *
 * @return void
 */
function kalium_wpml_register_single_string( $name, $value ) {
	do_action( 'wpml_register_single_string', 'kalium', $name, $value );
}

/**
 * WPML dynamic translatable string.
 *
 * @param string $original_value
 * @param string $name
 *
 * @return string
 */
function kalium_wpml_translate_single_string( $original_value, $name ) {
	return apply_filters( 'wpml_translate_single_string', $original_value, 'kalium', $name );
}

/**
 * Share post to social networks.
 *
 * @param string   $social_network_id
 * @param null|int $post_id
 * @param array    $args
 */
function kalium_social_network_share_post_link( $social_network_id, $post_id = null, $args = [] ) {
	$post = get_post( $post_id );

	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	// Link args
	$args = wp_parse_args( $args, [
		'icon_only' => false,
		'class'     => '',
	] );

	/**
	 * Filters list of providers for social networks share.
	 *
	 * @param array $networks_list
	 * @param int   $post_id
	 */
	$networks = apply_filters( 'kalium_social_network_share_post_link_providers', [
		'fb'  => [
			'id'      => 'facebook',
			'url'     => 'https://www.facebook.com/sharer.php?u={PERMALINK}',
			'tooltip' => 'Facebook',
			'icon'    => 'fab fa-facebook'
		],
		'tw'  => [
			'id'      => 'twitter',
			'url'     => 'https://twitter.com/share?text={TITLE}&url={PERMALINK}',
			'tooltip' => 'Twitter',
			'icon'    => 'fab fa-twitter'
		],
		'tlr' => [
			'id'      => 'tumblr',
			'url'     => 'https://www.tumblr.com/share/link?url={PERMALINK}&name={TITLE}&description={EXCERPT}',
			'tooltip' => 'Tumblr',
			'icon'    => 'fab fa-tumblr'
		],
		'lin' => [
			'id'      => 'linkedin',
			'url'     => 'https://linkedin.com/shareArticle?mini=true&url={PERMALINK}&title={TITLE}',
			'tooltip' => 'LinkedIn',
			'icon'    => 'fab fa-linkedin'
		],
		'pi'  => [
			'id'      => 'pinterest',
			'url'     => 'https://pinterest.com/pin/create/button/?url={PERMALINK}&description={TITLE}&media={FEATURED_IMAGE}',
			'tooltip' => 'Pinterest',
			'icon'    => 'fab fa-pinterest'
		],
		'vk'  => [
			'id'      => 'vk',
			'url'     => 'https://vkontakte.ru/share.php?url={PERMALINK}&title={TITLE}&description={EXCERPT}',
			'tooltip' => 'VKontakte',
			'icon'    => 'fab fa-vk'
		],
		'wa'  => [
			'id'      => 'whatsapp',
			'url'     => 'https://api.whatsapp.com/send?text={TITLE} - {PERMALINK}',
			'tooltip' => 'WhatsApp',
			'icon'    => 'fab fa-whatsapp'
		],
		'xi'  => [
			'id'      => 'xing',
			'url'     => 'https://www.xing.com/spi/shares/new?url={PERMALINK}',
			'tooltip' => 'Xing',
			'icon'    => 'fab fa-xing',
		],
		'pr'  => [
			'id'      => 'print',
			'url'     => 'javascript:window.print();',
			'tooltip' => __( 'Print', 'kalium' ),
			'icon'    => 'fas fa-print'
		],
		'em'  => [
			'id'      => 'mail',
			'url'     => 'mailto:?subject={TITLE}&body={EMAIL_BODY}',
			'tooltip' => __( 'Email', 'kalium' ),
			'icon'    => 'fas fa-envelope'
		],
	], $post_id );

	// Network entry exists
	if ( $network_entry = kalium_get_array_key( $networks, $social_network_id ) ) {

		// Share URL
		$url = $network_entry['url'];

		// URL vars to replace
		$url_vars = [
			'PERMALINK'      => get_permalink( $post ),
			'TITLE'          => get_the_title( $post ),
			'EXCERPT'        => wp_trim_words( kalium_clean_excerpt( $post->post_excerpt, true ), 40, '&hellip;' ),
			'FEATURED_IMAGE' => wp_get_attachment_url( get_post_thumbnail_id( $post ) ),
			'EMAIL_BODY'     => sprintf( __( 'Check out what I just spotted: %s', 'kalium' ), get_permalink( $post ) ),
		];

		foreach ( $url_vars as $var_name => $value ) {
			$url = str_replace( '{' . $var_name . '}', $value, $url );
		}

		// Link attributes
		$link_atts = [
			'class'      => [
				$network_entry['id'],
				$args['class'],
			],
			'href'       => esc_url( $url ),
			'target'     => '_blank',
			'aria-label' => $network_entry['tooltip'],
		];

		// Content
		$link_content = esc_html( $network_entry['tooltip'] );

		// Show icon only
		if ( $args['icon_only'] ) {
			$link_content = kalium()->helpers->build_dom_element( 'i', [
				'class' => [
					'icon',
					$network_entry['icon'],
				],
			] );
		}

		/**
		 * Filters social network share link markup.
		 *
		 * @param string   $link
		 * @param int|null $post_id
		 * @param array    $args
		 */
		echo apply_filters( 'kalium_social_network_share_post_link', kalium()->helpers->build_dom_element( 'a', $link_atts, $link_content ), $post_id, $args );
	}
}

/**
 * Search page url.
 *
 * @return string
 */
function kalium_search_url() {
	global $polylang;

	// Default search page URL
	$url = home_url( '/' );

	// Polylang Search URL
	if ( ! empty( $polylang ) ) {
		$url = $polylang->curlang->search_url;
	}

	return apply_filters( 'kalium_search_url', $url );
}
