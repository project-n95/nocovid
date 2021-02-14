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

class Kalium_Images {

	/**
	 * Content before image
	 */
	public $before_image = '';

	/**
	 * Content after image
	 */
	public $after_image = '';

	/**
	 * Lazy loading
	 */
	public $lazy_load = true;

	/**
	 * Crop gifs
	 */
	public $crop_gifs = false;

	/**
	 * Fluid width
	 */
	public $fluid_width = true;

	/**
	 * Placeholder background color
	 */
	public $placeholder_background = '';

	/**
	 * Placeholder gradient
	 */
	public $placeholder_gradient = null;

	/**
	 * Placeholder dominant color
	 */
	public $placeholder_dominant_color = false;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Get attachment image.
	 *
	 * @param int|string $attachment_id
	 * @param string     $size
	 * @param null       $atts
	 * @param array      $placeholder_atts
	 *
	 * @return string
	 */
	public function get_image( $attachment_id, $size = 'thumbnail', $atts = null, $placeholder_atts = [] ) {

		// Image
		if ( is_numeric( $attachment_id ) ) {

			// Load original gif image if enabled
			if ( false === $this->crop_gifs && 'image/gif' === get_post_mime_type( $attachment_id ) ) {
				$size = 'full';
			}

			$image         = wp_get_attachment_image( $attachment_id, $size, false, $atts );
			$wp_attachment = true;

			if ( ! $image ) {
				return '';
			}
		} // Parse an image
		else if ( false !== strpos( $attachment_id, '<img' ) ) {
			static $image_id = 1;

			$image         = $attachment_id;
			$attachment_id = $image_id ++;
		} // Get local image
		else {
			$local_image = $this->get_local_image( $attachment_id );

			// Local image found
			if ( $local_image ) {
				$image = $local_image['image'];
			} else {
				return '';
			}
		}

		$image_arr  = kalium()->helpers->parse_attributes( $image );
		$image_atts = $image_arr['attributes'];

		// Insert missing alt
		if ( ! isset( $image_atts['alt'] ) ) {
			$image_atts['alt'] = '';
		}

		// If height is missing...
		if ( empty( $image_atts['height'] ) && ! empty( $image_atts['src'] ) ) {
			$local_image = $this->get_local_image( $image_atts['src'] );

			if ( ! empty( $local_image['height'] ) ) {
				$image_atts['height'] = $local_image['height'];
			}
		}

		// Image options
		$image_opts = [
			'class'  => 'img-' . $attachment_id,
			'width'  => $image_atts['width'],
			'height' => $image_atts['height'],
		];

		// Classes
		if ( empty( $image_atts['class'] ) ) {
			$image_atts['class'] = '';
		}

		$image_atts['class'] .= " {$image_opts['class']}";

		// Lazy load
		if ( $this->lazy_load ) {

			// Image element
			foreach ( [ 'src', 'srcset', 'sizes' ] as $attr ) {
				if ( ! empty( $image_atts[ $attr ] ) ) {
					$image_atts[ 'data-' . $attr ] = $image_atts[ $attr ];
					unset( $image_atts[ $attr ] );
				}
			}

			$image_atts['class'] .= ' lazyload';
		}

		// Dominant image background color
		if ( $this->placeholder_dominant_color && isset( $wp_attachment ) ) {
			if ( is_numeric( $attachment_id ) ) {
				$dominant_color = $this->get_dominant_color( $attachment_id );
			} else if ( ! empty( $local_image ) ) {
				$dominant_color = $this->get_dominant_color_from_file( $local_image );
			}

			if ( $dominant_color ) {
				$image_opts['background'] = $dominant_color;
			}
		}

		// Image
		$image = kalium()->helpers->build_dom_element( [
			'tag_name'   => 'img',
			'attributes' => $image_atts,
		] );

		return $this->aspect_ratio_wrap( $image, $image_opts, $placeholder_atts );
	}

	/**
	 * Get image by URL
	 *
	 * @param string $image_url
	 *
	 * @return array
	 */
	private function get_local_image( $image_url ) {
		$uploads_dir = wp_upload_dir();
		$site_url    = site_url();

		$image   = [];
		$baseurl = $uploads_dir['baseurl'];

		// Extract from tag
		if ( preg_match( '#<img[^>]+>#', $image_url, $matches ) ) {
			$element            = kalium()->helpers->parse_attributes( $matches[0] );
			$element_attributes = $element['attributes'];

			if ( ! empty( $element_attributes['src'] ) ) {
				$image_url = $element_attributes['src'];

				if ( ! empty( $element_attributes['alt'] ) ) {
					$alt = $element_attributes['alt'];
				}
			}
		}

		// Check for local image
		if ( false !== strpos( $image_url, $site_url ) ) {
			$image_relative_path = ltrim( str_replace( $site_url, '', $image_url ), '\/' );
			$image_full_path     = ABSPATH . $image_relative_path;

			if ( file_exists( $image_full_path ) ) {
				$image_size = @getimagesize( $image_full_path );

				if ( is_array( $image_size ) && isset( $image_size[0] ) && isset( $image_size[1] ) ) {
					$image_element = sprintf( '<img src="%s" width="%d" height="%d" alt="%s" />', $image_url, $image_size[0], $image_size[1], esc_attr( isset( $alt ) ? $alt : basename( $image_url ) ) );

					$image = [
						'src'    => $image_url,
						'path'   => $image_full_path,
						'width'  => $image_size[0],
						'height' => $image_size[1],
						'image'  => $image_element
					];
				}
			}
		} else {
			// Remote image
			$image_hash  = 'img_' . md5( $image_url );
			$image_sizes = get_option( 'kalium_remote_images_sizes', [] );

			if ( empty( $image_sizes[ $image_hash ] ) ) {
				$image_size = @getimagesize( $image_url );

				if ( is_array( $image_size ) ) {
					$image_size['time']         = time();
					$image_sizes[ $image_hash ] = $image_size;

					update_option( 'kalium_remote_images_sizes', $image_sizes );
				}
			} else {
				$image_size = $image_sizes[ $image_hash ];
			}

			if ( is_array( $image_size ) && isset( $image_size[0] ) && isset( $image_size[1] ) ) {
				$image_element = sprintf( '<img src="%s" width="%d" height="%d" alt="%s" />', $image_url, $image_size[0], $image_size[1], esc_attr( basename( $image_url ) ) );

				$image = [
					'src'    => $image_url,
					'path'   => '',
					'width'  => $image_size[0],
					'height' => $image_size[1],
					'image'  => $image_element
				];
			}
		}


		return $image;
	}

	/**
	 * Build image style
	 *
	 * @param array $style
	 *
	 * @return string
	 */
	private function build_image_style( $style ) {
		$style_arr = [];

		foreach ( $style as $prop => $value ) {
			$style_arr[] = sprintf( '%s:%s', $prop, $value );
		}

		return implode( ';', $style_arr );
	}

	/**
	 * Set lazy loading state.
	 *
	 * @param bool $new_state
	 */
	public function set_lazy_loading( $new_state = true ) {
		$this->lazy_load = $new_state;
	}

	/**
	 * Set placeholder color
	 *
	 * @param string $color
	 */
	public function set_placeholder_color( $color ) {
		$this->placeholder_background = $color;
	}

	/**
	 * Set fluid width mode
	 *
	 * @param bool $fluid
	 */
	public function set_fluid_width( $fluid = true ) {
		$this->fluid_width = $fluid ? true : false;
	}

	/**
	 * Set gradients
	 *
	 * @param string $start_color
	 * @param string $end_color
	 * @param string $type
	 */
	public function set_placeholder_gradient( $start_color = '', $end_color = '', $type = 'linear' ) {
		$this->placeholder_gradient = [
			'start' => $start_color,
			'end'   => $end_color,
			'type'  => $type
		];
	}

	/**
	 * Use dominant color.
	 *
	 * @param bool $use
	 */
	public function use_dominant_color( $use = true ) {
		$this->placeholder_dominant_color = $use;
	}

	/**
	 * Set loading spinner
	 *
	 * @param string $spinner_id
	 * @param array  $args
	 */
	public function set_loading_spinner( $spinner_id, $args = [] ) {

		// Spinner
		$spinner = new Kalium_Image_Loading_Spinner( $spinner_id, $args );

		if ( $spinner->get_spinner() ) {
			$this->before_image = (string) $spinner;
		}
	}

	/**
	 * Set custom preloader
	 *
	 * @param int   $attachment_id
	 * @param array $args
	 */
	public function set_custom_preloader( $attachment_id, $args ) {

		// Custom preloader
		$preloader = new Kalium_Image_Custom_Preloader( $attachment_id, $args );

		if ( $preloader->get_preloader_image() ) {
			$this->before_image = (string) $preloader;
		}
	}

	/**
	 * Calculate aspect ratio
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @return string
	 */
	public function calculate_aspect_ratio( $width, $height ) {
		if ( 0 == $width || 0 == $height ) {
			$width = $height = 1;
		}

		return number_format( $height / $width * 100, 8, '.', '' );
	}

	/**
	 * Image placeholder - aspect ratio wrapper
	 *
	 * @param string $element
	 * @param array  $opts
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function aspect_ratio_wrap( $element, $opts = [], $atts = [] ) {

		// Options
		$opts = array_merge( [
			'width'  => 1,
			'height' => 1
		], $opts );

		// Placeholder classes
		$placeholder_classes = [ 'image-placeholder' ];

		// Style
		$image_style = [];

		// Define proportional image height
		$image_style['padding-bottom'] = $this->calculate_aspect_ratio( $opts['width'], $opts['height'] ) . '%';

		// Background color
		if ( $this->placeholder_background ) {
			$image_style['background-color'] = $this->placeholder_background;
		}

		// Custom background image
		if ( ! empty( $opts['background'] ) ) {
			$image_style['background-color'] = $opts['background'];
		}

		// Gradient color
		if ( $this->placeholder_gradient ) {
			$start = $this->placeholder_gradient['start'];
			$end   = $this->placeholder_gradient['end'];
			$type  = $this->placeholder_gradient['type'];

			$gradient_color = $this->get_gradient_background( $start, $end, $type );

			if ( $gradient_color ) {
				$image_style['background-image'] = $gradient_color;
			}
		}

		// Placeholder attributes
		$placeholder_atts = [
			'class' => '',
			'style' => $this->build_image_style( $image_style )
		];

		// Extend attributes
		if ( is_array( $atts ) && ! empty( $atts ) ) {
			foreach ( $atts as $attr_id => $attr_value ) {
				if ( 'class' == $attr_id ) {
					$new_classes         = is_array( $attr_value ) ? $attr_value : explode( ' ', $attr_value );
					$placeholder_classes = array_unique( array_merge( $placeholder_classes, $new_classes ) );
				} else if ( 'style' !== strtolower( $attr_id ) ) {
					$placeholder_atts[ $attr_id ] = $attr_value;
				}
			}
		}

		// Placeholder classes
		$placeholder_atts['class'] = kalium()->helpers->list_classes( $placeholder_classes );

		// Create placeholder attributes array
		$placeholder_atts_str = [];

		foreach ( $placeholder_atts as $attr_id => $attr_value ) {
			$placeholder_atts_str[] = sprintf( '%s="%s"', esc_attr( $attr_id ), esc_attr( $attr_value ) );
		}

		// Video wrapper start
		$placeholder_wrapper = sprintf( '<span %s>', implode( ' ', $placeholder_atts_str ) );

		// Before
		$placeholder_wrapper .= apply_filters( 'kalium_images_before_image', $this->before_image );

		// Content element
		$placeholder_wrapper .= $element;

		// After
		$placeholder_wrapper .= apply_filters( 'kalium_images_after_image', $this->after_image );

		// Video wrapper end	
		$placeholder_wrapper .= '</span>';

		// Maximum width applied
		if ( ! $this->fluid_width ) {
			$placeholder_wrapper = sprintf( '<span class="realsize-image-placeholder" style="max-width:%2$dpx;">%1$s</span>', $placeholder_wrapper, $opts['width'] );
		}

		return $placeholder_wrapper;
	}

	/**
	 * Get Dominant Image Color.
	 *
	 * @param int $attachment_id
	 *
	 * @return string
	 */
	public function get_dominant_color( $attachment_id ) {
		$dominant_color = '';
		$metadata       = wp_get_attachment_metadata( $attachment_id );

		if ( ! isset( $metadata['image_meta'] ) ) {
			return $dominant_color;
		}

		// Retrieve dominant color
		if ( empty( $metadata['image_meta']['kalium_dominant_color'] ) ) {
			require_once kalium()->locate_file( 'includes/libraries/class-dominantcolors.php' );
			$dominant_colors = kalium_get_dominant_colors( $attachment_id, [ 'colorsNum' => 1 ] );

			if ( ! empty( $dominant_colors['foundColors'] ) ) {
				$dominant_color                                  = $dominant_colors['foundColors'][0];
				$metadata['image_meta']['kalium_dominant_color'] = $dominant_color;
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		}

		// Assign Dominant color
		if ( ! empty( $metadata['image_meta']['kalium_dominant_color'] ) ) {
			$dominant_color = $metadata['image_meta']['kalium_dominant_color'];
		}

		return $dominant_color;
	}

	/**
	 * Get Dominant omage color from direct file.
	 *
	 * @param array $image
	 *
	 * @return mixed|null
	 */
	private function get_dominant_color_from_file( $image ) {
		if ( empty( $image['path'] ) ) {
			return null;
		}

		$image_hash      = 'img_' . md5( $image['path'] );
		$dominant_colors = get_option( 'kalium_images_dominant_colors', [] );

		if ( isset( $dominant_colors[ $image_hash ] ) ) {
			return $dominant_colors[ $image_hash ];
		} else {
			require_once kalium()->locate_file( 'includes/libraries/class-dominantcolors.php' );

			$colors               = new DominantColors( $image['path'], [ 'colorsNum' => 1 ] );
			$colors_most_dominant = $colors->getDominantColors();

			if ( ! empty( $colors_most_dominant['foundColors'] ) ) {
				$dominant_color = $colors_most_dominant['foundColors'][0];
			}

			// Set dominant color
			$dominant_colors[ $image_hash ] = isset( $dominant_color ) ? $dominant_color : '';

			update_option( 'kalium_images_dominant_colors', $dominant_colors );

			return $dominant_color;
		}
	}

	/**
	 * Get gradient colors.
	 */
	private function get_gradient_background( $start, $end, $type = 'linear' ) {
		$gradient_color = '';

		if ( $start && $end ) {
			if ( 'radial' == $type ) {
				$gradient_color = "radial-gradient(circle, {$start}, {$type} 60%)";
			} else {
				$gradient_color = "linear-gradient(to bottom, {$start}, {$end})";
			}
		}

		return $gradient_color;
	}
}

/**
 * Kalium Image Loading Spinner
 */
class Kalium_Image_Loading_Spinner {

	/**
	 * Spinner ID
	 */
	public $spinner_id = '';

	/**
	 * Stored args
	 */
	public $args = [];

	/**
	 * Constructor
	 *
	 * @param string $spinner_id
	 * @param array  $args
	 */
	public function __construct( $spinner_id, $args = [] ) {

		// Spinner
		$this->spinner_id = $spinner_id;

		// Args
		$args = shortcode_atts( [
			'holder'    => 'span',
			'alignment' => 'center',
			'spacing'   => '',
			'color'     => '',
			'scale'     => ''
		], $args );

		$this->args = $args;

		// Generate HTML
		$this->html       = $this->get_html();
		$this->css        = $this->get_css();
		$this->css_parsed = false;
	}

	/**
	 * Get Spinner
	 */
	public function get_spinner() {
		$spinners = self::get_spinners();

		return isset( $spinners[ $this->spinner_id ] ) ? $spinners[ $this->spinner_id ] : null;
	}

	/**
	 * Get CSS
	 */
	public function get_css() {
		$css        = [];
		$spinner_id = $this->spinner_id;
		$args       = $this->args;

		// Spacing
		$spacing = $this->args['spacing'];

		if ( $spacing >= 0 ) {
			$css[] = sprintf( '.image-placeholder > .loader { %s }', "left:{$args['spacing']}px;right:{$args['spacing']}px;top:{$args['spacing']}px;bottom:{$args['spacing']}px;" );
		}

		// Scale
		$scale = $args['scale'];

		if ( $scale ) {
			$transform = "scale3d({$args['scale']},{$args['scale']},1)";
			$css[]     = sprintf( '.image-placeholder > .loader .loader-row .loader-size { %s }', "transform:{$transform};-webkit-transform:{$transform};-moz-transform:{$transform};" );
		}

		// Color
		$color = $args['color'];

		if ( $color ) {

			$loaders_selectors = [
				'background-color'    => [
					'.ball-scale > span',
					'.ball-scale-multiple > span',
					'.ball-scale-random > span',
					'.ball-clip-rotate-pulse > span:first-child',
					'.line-scale > span',
					'.line-scale-party > span',
					'.line-scale-pulse-out > span',
					'.line-scale-pulse-out-rapid > span',
					'.ball-pulse-sync > span',
					'.ball-pulse > span',
					'.ball-beat > span',
					'.ball-rotate > span',
					'.ball-rotate > span:before',
					'.ball-rotate > span:after',
					'.ball-spin-fade-loader > span',
					'.line-spin-fade-loader > span',
					'.ball-grid-pulse > span',
					'.ball-grid-beat > span',
					'.pacman > span:nth-child(3)',
					'.pacman > span:nth-child(4)',
					'.pacman > span:nth-child(5)',
					'.pacman > span:nth-child(6)',
					'.square-spin > span',
					'.ball-pulse-rise > span',
					'.cube-transition > span',
					'.ball-zig-zag > span',
					'.ball-zig-zag-deflect > span',
					'.square-spin > span'
				],
				'background-image'    => [
					'.semi-circle-spin > span' => 'linear-gradient(transparent 0%, transparent 70%, {color} 30%, {color} 100%)'
				],
				'border-color'        => [
					'.ball-clip-rotate > span',
					'.ball-scale-ripple > span',
					'.ball-scale-ripple-multiple > span',
					'.ball-clip-rotate-multiple > span',
					'.ball-triangle-path > span',
					'.double-circle-rotate > span',
					'.circle-pulse > span',
				],
				'border-top-color'    => [
					'.ball-clip-rotate-pulse > span:last-child',
					'.ball-clip-rotate-multiple > span:last-child',
					'.pacman > span:first-of-type',
					'.pacman > span:nth-child(2)',
				],
				'border-bottom-color' => [
					'.ball-clip-rotate-pulse > span:last-child',
					'.ball-clip-rotate-multiple > span:last-child',
					'.triangle-skew-spin > span',
					'.pacman > span:first-of-type',
					'.pacman > span:nth-child(2)',
					'.ball-clip-rotate > span'     => 'transparent',
					'.double-circle-rotate > span' => 'transparent',
				],
				'border-left-color'   => [
					'.pacman > span:first-of-type',
					'.pacman > span:nth-child(2)'
				],
				'stroke'              => [
					'.modern-circular .circular .path'
				]
			];

			foreach ( $loaders_selectors as $css_property => $selectors ) {

				foreach ( $selectors as $key => $selector ) {
					if ( is_string( $key ) ) {
						$id = explode( ' ', $key );
						$id = str_replace( '.', '', $id[0] );

						if ( $id == $spinner_id ) {
							$props = $css_property . ':' . str_replace( '{color}', $args['color'], $selector );
							$css[] = sprintf( '%s { %s }', $key, $props );
						}
					} else {
						$id = explode( ' ', $selector );
						$id = str_replace( '.', '', $id[0] );

						if ( $id == $spinner_id ) {
							$css[] = sprintf( '%s { %s }', $selector, $css_property . ':' . $args['color'] );
						}
					}
				}
			}
		}

		return '<style>' . implode( ' ', $css ) . '</style>';
	}

	/**
	 * Get HTML spinner.
	 *
	 * @return string
	 */
	public function get_html() {
		$spinner_id = $this->spinner_id;
		$spinner    = $this->get_spinner();
		$args       = $this->args;

		// Classes
		$spinner_classes = [ 'loader' ];

		if ( $args['alignment'] ) {
			$spinner_classes[] = "align-{$args['alignment']}";
		}

		$spinner_html = '<' . $args['holder'] . ' class="' . implode( ' ', $spinner_classes ) . '" data-id="' . $spinner_id . '">';

		$spinner_html .= '<' . $args['holder'] . ' class="loader-row">';

		if ( $args['scale'] ) {
			$spinner_html .= '<' . $args['holder'] . ' class="loader-size">';
		}

		$spinner_html .= '<' . $args['holder'] . ' class="loader-inner ' . $spinner_id . '">';

		if ( isset( $spinner['markup'] ) ) {
			$spinner_html .= $spinner['markup'];
		} else {
			$spinner_html .= str_repeat( '<span></span>', $spinner['layers'] );
		}

		$spinner_html .= '</' . $args['holder'] . '>';


		if ( $args['scale'] ) {
			$spinner_html .= '</' . $args['holder'] . '>';
		}

		$spinner_html .= '</' . $args['holder'] . '>';

		$spinner_html .= '</' . $args['holder'] . '>';

		return $spinner_html;
	}

	/**
	 * Registered spinners.
	 *
	 * @return array
	 */
	public static function get_spinners() {
		$loading_spinners = [
			'double-circle-rotate' => [ 'name' => 'Double Circle Rotate', 'layers' => 2 ],
			'modern-circular'      => [
				'name'   => 'Modern Circular Loader',
				'markup' => '<svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"/></svg>'
			],
			'circle-pulse'         => [ 'name' => 'Circle Pulse', 'layers' => 1 ],

			'ball-clip-rotate'           => [ 'name' => 'Ball Clip Rotate', 'layers' => 1 ],
			'ball-scale'                 => [ 'name' => 'Ball Scale', 'layers' => 1 ],
			'ball-scale-multiple'        => [ 'name' => 'Ball Scale Multiple', 'layers' => 3 ],
			'ball-scale-ripple'          => [ 'name' => 'Ball Scale Ripple', 'layers' => 1 ],
			'ball-scale-ripple-multiple' => [ 'name' => 'Ball Scale Ripple Multiple', 'layers' => 3 ],
			'ball-scale-random'          => [ 'name' => 'Ball Scale Random', 'layers' => 3 ],
			'ball-clip-rotate-pulse'     => [ 'name' => 'Ball Clip Rotate Pulse', 'layers' => 2 ],
			'ball-clip-rotate-multiple'  => [ 'name' => 'Ball Clip Rotate Multiple', 'layers' => 2 ],

			'line-scale'                 => [ 'name' => 'Line Scale', 'layers' => 5 ],
			'line-scale-party'           => [ 'name' => 'Line Scale Party', 'layers' => 4 ],
			'line-scale-pulse-out'       => [ 'name' => 'Line Scale Pulse Out', 'layers' => 5 ],
			'line-scale-pulse-out-rapid' => [ 'name' => 'Line Scale Pulse Out Rapid', 'layers' => 5 ],

			'ball-pulse-sync' => [ 'name' => 'Ball Pulse Sync', 'layers' => 3 ],
			'ball-pulse'      => [ 'name' => 'Ball Pulse', 'layers' => 3 ],


			'ball-beat'             => [ 'name' => 'Ball Beat', 'layers' => 3 ],
			'ball-rotate'           => [ 'name' => 'Ball Rotate', 'layers' => 1 ],
			'ball-spin-fade-loader' => [ 'name' => 'Ball Spin Fade Loader', 'layers' => 8 ],
			'line-spin-fade-loader' => [ 'name' => 'Line Spin Fade Loader', 'layers' => 8 ],
			'ball-grid-pulse'       => [ 'name' => 'Ball Grid Pulse', 'layers' => 9 ],
			'ball-grid-beat'        => [ 'name' => 'Ball Grid Beat', 'layers' => 9 ],

			'triangle-skew-spin' => [ 'name' => 'Triangle Skew Spin', 'layers' => 1 ],
			'pacman'             => [ 'name' => 'Pacman', 'layers' => 5 ],
			'semi-circle-spin'   => [ 'name' => 'Semi Circle Spin', 'layers' => 1 ],


			'square-spin'          => [ 'name' => 'Square Spin', 'layers' => 1 ],
			'ball-pulse-rise'      => [ 'name' => 'Ball Pulse Rise', 'layers' => 5 ],
			'cube-transition'      => [ 'name' => 'Cube Transition', 'layers' => 2 ],
			'ball-zig-zag'         => [ 'name' => 'Ball Zig Zag', 'layers' => 2 ],
			'ball-zig-zag-deflect' => [ 'name' => 'Ball Zig Zag Deflect', 'layers' => 2 ],
			'ball-triangle-path'   => [ 'name' => 'Ball Triangle Path', 'layers' => 3 ],
		];

		return $loading_spinners;
	}

	/**
	 * Get spinner (static context)
	 *
	 * @param string $id
	 * @param array  $args
	 *
	 * @return Kalium_Image_Loading_Spinner
	 */
	public static function get_spinner_by_id( $id, $args = [] ) {
		$spinner = new self( $id, $args );

		return $spinner;
	}

	/**
	 * Output in the screen
	 */
	public function __toString() {
		if ( ! $this->css_parsed ) {
			$this->css_parsed = true;

			return $this->css . $this->html;
		}

		return $this->html;
	}
}

/**
 * Kalium Image Custom Preloader
 */
class Kalium_Image_Custom_Preloader {

	/**
	 * Preloader ID.
	 */
	public $attachment_id = '';

	/**
	 * Constructor
	 *
	 * @param int   $attachment_id
	 * @param array $args
	 */
	public function __construct( $attachment_id, $args = [] ) {

		// Spinner
		$this->attachment_id = $attachment_id;

		// Args
		$args = shortcode_atts( [
			'width'     => '',
			'alignment' => 'center',
			'spacing'   => ''
		], $args );

		$this->args = $args;

		// Generate HTML
		$this->html       = $this->get_html();
		$this->css        = $this->get_css();
		$this->css_parsed = false;
	}

	/**
	 * Get preloader image.
	 *
	 * @return string
	 */
	public function get_preloader_image() {
		return wp_get_attachment_image( $this->attachment_id, 'full' );
	}

	/**
	 * Get CSS.
	 *
	 * @return string
	 */
	public function get_css() {
		$css  = [];
		$args = $this->args;

		// Spacing
		$spacing = $this->args['spacing'];

		if ( $spacing >= 0 ) {
			$css[] = sprintf( '.image-placeholder > .custom-preloader-image { %s }', "padding:{$args['spacing']}px;" );
		}

		// Width
		$width = $this->args['width'];

		if ( $width >= 0 ) {
			$css[] = sprintf( '.image-placeholder > .custom-preloader-image { %s }', "width:{$args['width']}px;" );
		}

		return '<style>' . implode( PHP_EOL, $css ) . '</style>';
	}

	/**
	 * Get HTML
	 */
	public function get_html() {
		$args = $this->args;

		$classes   = [ 'custom-preloader-image' ];
		$classes[] = 'align-' . $args['alignment'];

		$image = $this->get_preloader_image();

		return sprintf( '<span class="%s">%s</span>', implode( ' ', $classes ), $image );
	}

	/**
	 * Output in the screen
	 */
	public function __toString() {
		if ( ! $this->css_parsed ) {
			$this->css_parsed = true;

			return $this->css . $this->html;
		}

		return $this->html;
	}
}
