<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_VideoJS {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Init
		add_action( 'init', [ $this, 'init' ], 10 );

		// Video and audio processing library
		add_filter( 'wp_video_shortcode_library', [ $this, 'library' ], 10 );
		add_filter( 'wp_audio_shortcode_library', [ $this, 'library' ], 10 );

		// Video and audio element classes
		add_filter( 'wp_video_shortcode_class', [ $this, 'classes' ], 10 );
		add_filter( 'wp_audio_shortcode_class', [ $this, 'classes' ], 10 );

		// Embed defaults
		add_filter( 'embed_defaults', [ $this, 'embed_defaults' ], 10, 2 );

		// Default media parameters
		add_filter( 'kalium_embed_video_atts', [ $this, 'media_parameters' ], 10, 2 );
		add_filter( 'kalium_embed_audio_atts', [ $this, 'media_parameters' ], 10, 2 );

		// Youtube and Vimeo handlers
		if ( ! $this->is_in_edit_mode() ) {
			add_filter( 'embed_oembed_html', [ $this, 'youtube_handler' ], 10, 4 );
			add_filter( 'embed_oembed_html', [ $this, 'vimeo_handler' ], 10, 4 );

			// Video shortcode
			add_filter( 'wp_video_shortcode', [ $this, 'video_handler' ], 10, 4 );

			// Audio shortcode
			add_filter( 'wp_audio_shortcode', [ $this, 'audio_handler' ], 10, 4 );
		}
	}

	/**
	 * Init
	 */
	public function init() {

		// Video JS Share option enabling from theme options
		if ( 'yes' == kalium_get_theme_option( 'videojs_share' ) ) {
			add_filter( 'kalium_videojs_share', '__return_true' );
		}

		// Video JS
		wp_register_script( 'video-js', kalium()->assets_url( 'js/video-js/video.min.js' ), null, null, true );
		wp_register_style( 'video-js', kalium()->assets_url( 'js/video-js/video-js.min.css' ), null, null );

		wp_register_script( 'video-js-youtube', kalium()->assets_url( 'js/video-js-youtube.js' ), [ 'video-js' ], null, true );

		// Vimeo Player
		wp_register_script( 'vimeo-player', kalium()->assets_url( 'js/vimeo/player.min.js' ), null, null, true );

		// Video JS Share
		wp_register_script( 'video-js-share', kalium()->assets_url( 'js/video-js-share/videojs-share.min.js' ), [ 'video-js' ], null, true );
		wp_register_style( 'video-js-share', kalium()->assets_url( 'js/video-js-share/videojs-share.css' ), [ 'video-js' ], null, null );

		// Use native YouTube player
		if ( 'native' == kalium_get_theme_option( 'youtube_player' ) ) {
			add_filter( 'kalium_videojs_native_youtube_player', '__return_true', 100 );
		}
	}

	/**
	 * Is in edit mode (backend)
	 */
	private function is_in_edit_mode() {
		global $pagenow;

		return defined( 'DOING_AJAX' ) && in_array( kalium()->request->query( 'action' ), [
				'parse-embed',
				'parse-media-shortcode',
			] ) || ( is_admin() && in_array( $pagenow, [ 'post.php' ] ) );
	}

	/**
	 * Enqueue library
	 */
	public function enqueue() {
		static $videojs_imported;

		if ( $videojs_imported ) {
			return;
		}

		$videojs_imported = true;

		// VideoJS
		kalium_enqueue( 'videojs' );

		// Youtube extension
		kalium_enqueue( 'videojs-youtube-js' );

		// Vimeo
		kalium_enqueue( 'vimeo-player-js' );

		// Share option
		if ( apply_filters( 'kalium_videojs_share', false ) ) {
			$share_options = apply_filters( 'kalium_videojs_share_options', [
				'socials' => [
					'fb',
					'tw',
					'reddit',
					'messenger',
					'linkedin',
					'telegram',
					'whatsapp',
					'viber',
					'vk',
					'ok',
					'mail'
				],
				'fbAppId' => '',
			] );

			// JS Variable for share options
			kalium_define_js_variable( 'videojs_share_options', $share_options );

			// Share extension
			kalium_enqueue( 'videojs-share' );
		}
	}

	/**
	 * Shortcode library
	 */
	public function library() {
		return 'video-js';
	}

	/**
	 * Embed defaults
	 *
	 * @param $defaults
	 * @param $url
	 *
	 * @return array
	 */
	public function embed_defaults( $defaults, $url ) {

		if ( kalium()->media->is_youtube( $url ) || kalium()->media->is_vimeo( $url ) ) {
			return [ 'width' => 560, 'height' => 315 ];
		}

		return $defaults;
	}

	/**
	 * Media parameters
	 *
	 * @param $atts
	 * @param $media_element
	 *
	 * @return mixed
	 */
	public function media_parameters( $atts, $media_element ) {
		$preload  = kalium_get_theme_option( 'videojs_player_preload' );
		$autoplay = kalium_get_theme_option( 'videojs_player_autoplay' );
		$loop     = 'yes' == kalium_get_theme_option( 'videojs_player_loop' );

		// Preload
		$atts['preload'] = $preload;

		// Autoplay
		if ( empty( $atts['autoplay'] ) ) {
			if ( 'on-viewport' == $autoplay ) {
				$atts['data-autoplay'] = 'on-viewport';
			} else if ( 'yes' == $autoplay ) {
				$atts['autoplay'] = 'autoplay';
			}
		}

		// Loop
		if ( $loop && empty( $atts['loop'] ) ) {
			$atts['loop'] = 'loop';
		}

		return $atts;
	}

	/**
	 * Shortcode class
	 */
	public function classes( $_classes ) {
		$classes   = [ 'video-js-el' ];
		$classes[] = 'vjs-default-skin';

		if ( 'minimal' == kalium_get_theme_option( 'videojs_player_skin' ) ) {
			$classes[] = 'vjs-minimal-skin';
		}

		return trim( $_classes . ' ' . implode( ' ', $classes ) );
	}

	/**
	 * YouTube video handler
	 *
	 * @param $cache
	 * @param $url
	 * @param $atts
	 * @param $post_id
	 *
	 * @return string
	 */
	public function youtube_handler( $cache, $url, $atts, $post_id ) {

		// Use native YouTube player
		if ( apply_filters( 'kalium_videojs_native_youtube_player', false ) ) {
			return kalium()->media->embed_youtube( $url, $atts );
		}

		if ( kalium()->media->is_youtube( $url ) ) {

			// Enqueue VideoJS library including YouTube Extension
			$this->enqueue();

			// YouTube video attributes
			$youtube_atts = [
				'data-vsetup' => [
					'techOrder' => [ 'youtube' ],
					'sources'   => [
						[
							'type' => 'video/youtube',
							'src'  => $url
						]
					],
					'youtube'   => [
						'iv_load_policy' => 1,
						'ytControls'     => 3,

						'customVars'                => [
							'wmode'    => 'transparent',
							'controls' => 0,
						],

						// Comply with GDPR regulations
						'enablePrivacyEnhancedMode' => "true"
					]
				],
			];


			$youtube_video = new Kalium_VideoJS_Media_Element( 'video', array_merge( $atts, $youtube_atts ) );

			return $youtube_video->get_element();
		}

		return $cache;
	}

	/**
	 * Vimeo video handler
	 *
	 * @param $cache
	 * @param $url
	 * @param $atts
	 * @param $post_id
	 *
	 * @return string
	 */
	public function vimeo_handler( $cache, $url, $atts, $post_id ) {

		if ( kalium()->media->is_vimeo( $url ) ) {

			return kalium()->media->embed_vimeo( $url, $atts );
		}

		return $cache;
	}

	/**
	 * Video shortcode
	 *
	 * @param $output
	 * @param $atts
	 * @param $video
	 * @param $post_id
	 *
	 * @return string
	 */
	public function video_handler( $output, $atts, $video, $post_id ) {
		global $wp_embed;

		// Youtube or Vimeo videos
		if ( ! empty( $atts['src'] ) && ( kalium()->media->is_youtube( $atts['src'] ) || kalium()->media->is_vimeo( $atts['src'] ) ) ) {

			// Pass shortcode atts to Embed element atts
			$embed_atts = [];

			foreach ( [ 'poster', 'loop', 'width', 'height', 'autoplay', 'preload' ] as $attr_id ) {
				if ( ! empty( $atts[ $attr_id ] ) ) {
					$embed_atts[ $attr_id ] = $atts[ $attr_id ];
				}
			}

			$embed_atts_hook_value = kalium_hook_merge_arrays( $embed_atts );
			add_filter( 'kalium_embed_video_atts', $embed_atts_hook_value, 100 );
			$output = $wp_embed->autoembed( $atts['src'] );
			remove_filter( 'kalium_embed_video_atts', $embed_atts_hook_value, 100 );

			return $output;
		}

		// Enqueue VideoJS library
		$this->enqueue();

		// Video attributes
		if ( preg_match( '/<video[^>]+>(.*?)<\/video>/', $output, $matches ) ) {

			// VSetup attribut
			$atts['data-vsetup'] = [];

			// Ommit empty attributes
			foreach ( [ 'poster', 'loop', 'autoplay', 'preload' ] as $attr_id ) {
				if ( empty( $atts[ $attr_id ] ) ) {
					unset( $atts[ $attr_id ] );
				}
			}

			// Remove unsupported attributes
			foreach ( [ 'm4v', 'webm', 'ogv', 'flv' ] as $attr_id ) {
				if ( isset( $atts[ $attr_id ] ) ) {
					unset( $atts[ $attr_id ] );
				}
			}

			$video_element = new Kalium_VideoJS_Media_Element( 'video', $atts, $matches[1] );

			return $video_element->get_element();
		}

		return $cache;
	}

	/**
	 * Audio shortcode
	 *
	 * @param $html
	 * @param $atts
	 * @param $audio
	 * @param $post_id
	 *
	 * @return string
	 */
	public function audio_handler( $html, $atts, $audio, $post_id ) {

		// Enqueue VideoJS library
		$this->enqueue();

		// Audio attributes
		if ( preg_match( '/<audio[^>]+>(.*?)<\/audio>/', $html, $matches ) ) {

			// VSetup attribut
			$atts['data-vsetup'] = [];

			// Remove empty attributes
			foreach ( [ 'poster', 'loop', 'autoplay', 'preload' ] as $attr_id ) {
				if ( empty( $atts[ $attr_id ] ) ) {
					unset( $atts[ $attr_id ] );
				}
			}

			// Remove unsupported attributes
			foreach ( [ 'mp3', 'ogg', 'flac', 'm4a', 'wav' ] as $attr_id ) {
				if ( isset( $atts[ $attr_id ] ) ) {
					unset( $atts[ $attr_id ] );
				}
			}

			// Default aspect ratio
			$atts['width']  = 16;
			$atts['height'] = 3;

			$audio_element = new Kalium_VideoJS_Media_Element( 'audio', $atts, $matches[1] );

			return $audio_element->get_element();
		}

		return $html;
	}

	/**
	 * Parse video or audio shortcode
	 *
	 * @param        $source
	 * @param string $atts
	 *
	 * @return string|null
	 */
	public function parse_media( $source, $atts = '' ) {

		// Set default scheme (HTTPS) for URLs
		if ( 'www.' === substr( $source, 0, 4 ) ) {
			$source = 'https://' . $source;
		}

		// Extract URLs
		$urls = wp_extract_urls( $source );

		// Match video shortcodes
		if ( preg_match( '#\[video[^\]]+\](\[\/video\])?#i', $source, $matches ) ) {
			$shortcode_video = do_shortcode( $matches[0] );

			if ( preg_match( '#(?<element><video[^>]+>)(?<content>.*?)(<\/video>)#i', $shortcode_video, $video_markup ) ) {
				$element = str_replace( ' src ', ' ', $video_markup['element'] );

				// Parse attributes
				$element_arr = kalium()->helpers->parse_attributes( $element );
				$atts        = array_merge( $element_arr['attributes'], $atts );

				$video_element = new Kalium_VideoJS_Media_Element( 'video', $atts, $video_markup['content'] );

				return $video_element->get_element();
			} else if ( preg_match( '#<iframe.*?src#i', $shortcode_video ) ) {
				return $shortcode_video;
			}

		} // Match audio shortcodes
		else if ( preg_match( '#\[audio[^\]]+\](\[\/audio\])?#i', $source, $matches ) ) {

			if ( preg_match( '#(?<element><audio[^>]+>)(?<content>.*?)(<\/audio>)#i', do_shortcode( $matches[0] ), $audio_markup ) ) {
				$element = str_replace( ' src ', ' ', $audio_markup['element'] );

				// Parse attributes
				$element_arr = kalium()->helpers->parse_attributes( $element );
				$atts        = array_merge( $element_arr['attributes'], $atts );

				$audio_element = new Kalium_VideoJS_Media_Element( 'audio', $atts, $audio_markup['content'] );

				return $audio_element->get_element();
			}
		} // Match Video or Audio URL
		else if ( ! empty( $urls[0] ) ) {

			// YouTube or Vimeo video
			if ( kalium()->media->is_youtube( $urls[0] ) || kalium()->media->is_vimeo( $urls[0] ) ) {
				$video_shortcode = sprintf( '[video src="%s"][/video]', esc_attr( $urls[0] ) );

				return $this->parse_media( $video_shortcode, $atts );
			} // Check other video types
			else {
				$video_types = wp_get_video_extensions();
				$audio_types = wp_get_audio_extensions();
				$type        = wp_check_filetype( preg_replace( '/\?.*/', '', $urls[0] ), wp_get_mime_types() );

				// Video URL
				if ( in_array( $type['ext'], $video_types ) ) {
					$shortcode = sprintf( '[video %s="%s"][/video]', esc_attr( $type['ext'] ), esc_attr( $urls[0] ) );

					return $this->parse_media( $shortcode, $atts );
				} // Audio URL
				else if ( in_array( $type['ext'], $audio_types ) ) {
					$shortcode = sprintf( '[audio %s="%s"][/audio]', esc_attr( $type['ext'] ), esc_attr( $urls[0] ) );

					return $this->parse_media( $shortcode, $atts );
				}
			}
		}

		return null;
	}
}

class Kalium_VideoJS_Media_Element {

	/**
	 * Element type
	 */
	public $type = 'video';

	/**
	 * Classes
	 */
	public $class_name = '';

	/**
	 * Element attributes
	 */
	public $atts = [];

	/**
	 * Element content
	 */
	public $content = '';

	/**
	 * Element width
	 */
	public $width = 1;

	/**
	 * Element height
	 */
	public $height = 1;

	/**
	 * Construct
	 *
	 * @param        $type
	 * @param array  $atts
	 * @param string $content
	 */
	public function __construct( $type, $atts = [], $content = '' ) {

		// Element type
		$this->type = 'audio' == $type ? 'audio' : 'video';

		// Element classes
		$default_classes = apply_filters( "wp_{$type}_shortcode_class", '' );

		// Filter attributes
		$this->atts = apply_filters( "kalium_embed_{$type}_atts", $atts, $this );

		// Filter content
		$this->content = apply_filters( "kalium_embed_{$type}_content", $content, $this );

		// Classes
		$this->class_name = $default_classes;

		if ( ! empty( $atts['class'] ) ) {
			$extra_classes    = is_array( $atts['class'] ) ? $atts['class'] : explode( ' ', $atts['class'] );
			$this->class_name = kalium()->helpers->list_classes( array_unique( array_merge( explode( ' ', $this->class_name ), $extra_classes ) ) );
		}

		// Width and height (used to generate aspect ratio element)
		if ( ! empty( $atts['width'] ) ) {
			$this->width = $atts['width'];
		}

		if ( ! empty( $atts['height'] ) ) {
			$this->height = $atts['height'];
		}
	}

	/**
	 * Build embed element
	 *
	 * @param        $tag_name
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function build_element( $tag_name, $atts, $content = '' ) {

		$atts_str = [];

		foreach ( $atts as $attr_name => $attr_value ) {

			if ( is_array( $attr_value ) ) {
				$attr_value = json_encode( $attr_value );
			}

			if ( ! is_numeric( $attr_name ) ) {
				if ( $attr_value ) {
					$atts_str[] = sprintf( '%1$s="%2$s"', esc_attr( $attr_name ), esc_attr( $attr_value ) );
				} else {
					$atts_str[] = esc_attr( $attr_name );
				}
			}
		}

		$element = sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag_name, implode( ' ', $atts_str ), $content );

		return $element;
	}

	/**
	 * Get DOM Element
	 */
	public function get_element() {

		// Clean attributes
		foreach ( [ 'class' ] as $attr_id ) {
			if ( isset( $this->atts[ $attr_id ] ) ) {
				unset( $this->atts[ $attr_id ] );
			}
		}

		// Attributes
		$atts = array_merge( [
			'controls' => '',
			'class'    => $this->class_name,
		], $this->atts );

		// Width and height
		$width  = $this->width;
		$height = $this->height;

		// Element
		$element = $this->build_element( $this->type, $atts, $this->content );

		return kalium()->images->aspect_ratio_wrap( $element, [
			'width'  => $width,
			'height' => $height
		], [ 'class' => 'video' ] );
	}

	/**
	 * To string
	 */
	public function __toString() {
		return $this->get_element();
	}
}
