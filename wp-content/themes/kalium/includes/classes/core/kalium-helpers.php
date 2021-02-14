<?php
/**
 * Kalium WordPress Theme
 *
 * Helper functions
 *
 * @link https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Helpers {

	/**
	 * Admin notices to show.
	 *
	 * @var array
	 */
	private $admin_notices = [];

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Hooks
		add_action( 'admin_init', [ $this, '_admin_init' ], 1000 );
	}

	/**
	 * Execute admin actions.
	 *
	 * @return void
	 */
	public function _admin_init() {

		// Show defined admin notices
		if ( count( $this->admin_notices ) ) {
			add_action( 'admin_notices', [ $this, '_show_admin_notices' ], 1000 );
		}
	}

	/**
	 * Show registered admin notices.
	 *
	 * @return void
	 */
	public function _show_admin_notices() {
		foreach ( $this->admin_notices as $i => $notice ) {
			$classes = [
				'laborator-notice',
				'notice',
				'notice-' . $notice['type'],
			];

			if ( $notice['dismissible'] ) {
				$classes[] = 'is-dismissible';
			}
			?>
            <div <?php kalium_class_attr( $classes ); ?>>
				<?php echo wpautop( $notice['message'] ); ?>
            </div>
			<?php
		}
	}

	/**
	 * Add admin notice.
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $dismissible
	 *
	 * @return void
	 */
	public function add_admin_notice( $message, $type = 'success', $dismissible = true ) {

		// Allowed notice types
		if ( ! in_array( $type, [ 'success', 'error', 'warning' ] ) ) {
			$type = 'info';
		}

		$this->admin_notices[] = [
			'message'     => $message,
			'type'        => $type,
			'dismissible' => $dismissible ? true : false
		];
	}

	/**
	 * Let to Num.
	 *
	 * @param int $size
	 *
	 * @return int
	 */
	public function let_to_num( $size ) {
		$l   = substr( $size, - 1 );
		$ret = (int) substr( $size, 0, - 1 );

		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			// No break.
			case 'T':
				$ret *= 1024;
			// No break.
			case 'G':
				$ret *= 1024;
			// No break.
			case 'M':
				$ret *= 1024;
			// No break.
			case 'K':
				$ret *= 1024;
			// No break.
		}

		return $ret;
	}

	/**
	 * Get SVG dimensions from viewBox.
	 *
	 * @param string $file
	 *
	 * @return array
	 */
	public function get_svg_dimensions( $file ) {
		$width = $height = 1;

		// Get attached file
		if ( is_numeric( $file ) ) {
			$file = get_attached_file( $file );
		}

		if ( function_exists( 'simplexml_load_file' ) ) {
			$svg = simplexml_load_file( $file );

			if ( isset( $svg->attributes()->viewBox ) ) {
				$view_box = explode( ' ', (string) $svg->attributes()->viewBox );
				$view_box = array_values( array_filter( array_map( 'absint', $view_box ) ) );

				if ( count( $view_box ) > 1 ) {
					return [ $view_box[0], $view_box[1] ];
				}
			}
		}

		return [ $width, $height ];
	}

	/**
	 * Add body class.
	 *
	 * @param string $classes
	 */
	public function add_body_class( $classes = '' ) {

		// This method has no effect after wp_head is executed
		if ( did_action( 'wp_head' ) && ! doing_action( 'wp_head' ) ) {
			kalium_doing_it_wrong( __FUNCTION__, 'The WP Hook "wp_head" was already executed.', '3.0' );
		}

		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}

		$classes = array_map( 'esc_attr', $classes );

		add_filter( 'body_class', kalium_hook_merge_array_value( implode( ' ', $classes ) ) );
	}

	/**
	 * List inline CSS classes, and escaped for an attribute.
	 *
	 * @param string|array $classes
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function list_classes( $classes, $echo = false ) {

		// Convert to array
		if ( ! is_array( $classes ) ) {
			$classes = [ $classes ];
		}

		// Classnames list
		$classes = trim( implode( ' ', array_map( 'esc_attr', $classes ) ) );

		if ( $echo ) {
			echo $classes;
		}

		return $classes;
	}

	/**
	 * Build DOM content element.
	 *
	 * @param string|array $tag_name
	 * @param array $attributes
	 * @param string $content
	 *
	 * @return string|void
	 */
	public function build_dom_element( $tag_name, $attributes = [], $content = '' ) {

		// Check for parsed element
		if ( is_array( $tag_name ) ) {
			$args = wp_parse_args( $tag_name, [
				'tag_name'   => '',
				'content'    => '',
				'attributes' => '',
				'echo'       => false,
			] );

			$tag_name   = $args['tag_name'];
			$attributes = $args['attributes'];
			$content    = $args['content'];
		}

		// If no tag is present
		if ( empty( $tag_name ) ) {
			return '';
		}

		// Self closing tags
		$self_closing_tags = [ 'img', 'br' ];

		// Attributes build
		$attributes_str = [];

		foreach ( $attributes as $attribute_name => $attribute_value ) {
			$attr_name  = sanitize_title( $attribute_name );

			// Class attribute
			if ( 'class' === $attribute_name && is_array( $attribute_value ) ) {
			    $attr_value = $this->list_classes( $attribute_value );
			} else { // Other attributes
				$attr_value = esc_attr( is_string( $attribute_value ) ? $attribute_value : json_encode( $attribute_value ) );
			}

			$attributes_str[] = sprintf( '%1$s="%2$s"', $attr_name, $attr_value );
		}

		// Self closing tag
		if ( in_array( strtolower( $tag_name ), $self_closing_tags ) ) {
			$element = sprintf( '<%s %s />', $tag_name, implode( ' ', $attributes_str ) );
		} else {
			$element = sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag_name, implode( ' ', $attributes_str ), $content );
		}

		// Echo element
		if ( isset( $args ) && $args['echo'] ) {
			echo $element;

			return;
		}

		return $element;
	}

	/**
	 * Build CSS props.
	 *
	 * @param array $props
	 *
	 * @return string
	 */
	public function build_css_props( $props ) {
		$props_str = [];

		foreach ( $props as $prop_name => $prop_value ) {
			if ( empty( $prop_value ) ) {
				continue;
			}
			$props_str[] = $prop_name . ':' . $prop_value;
		}

		return implode( ';', $props_str );
	}

	/**
	 * Parse attributes from an HTML element
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	public function parse_attributes( $input ) {
		$results = [
			'element'    => '',
			'attributes' => [],
			'content'    => '',
		];

		// Find nearest match
		if ( preg_match( '#^(<)(?<element>[a-zA-Z0-9\-._:]+)((\s)+(?<attributes>.*?))?((>)(?<content>[\s\S]*?)((<)\/\2(>))|(\s)*\/?(>))$#ms', $input, $matches ) ) {
			// Tag name and content
			$results['element'] = strtolower( $matches['element'] );
			$results['content'] = $matches['content'];

			// Attributes
			if ( ! empty( $matches['attributes'] ) && preg_match_all( '#(?<attribute_name>[a-z0-9\-_]+)=("|\')(?<attribute_value>[^"\']+)("|\')#im', $matches['attributes'], $matched_attributes ) ) {
				foreach ( $matched_attributes['attribute_name'] as $i => $attribute_name ) {
					$results['attributes'][ $attribute_name ] = $matched_attributes['attribute_value'][ $i ];
				}
			}
		}

		return $results;
	}

	/**
	 * Convert dashes to camel case.
	 *
	 * @param string $string
	 * @param bool $capitalize_first_character
	 *
	 * @return string
	 */
	public function dashes_to_camelcase( $string, $capitalize_first_character = false ) {
		$str = str_replace( '-', '', ucwords( $string, '-' ) );

		if ( ! $capitalize_first_character ) {
			$str = lcfirst( $str );
		}

		return $str;
	}

	/**
	 * Convert camel case to dashes.
	 *
	 * @param string $string
	 * @param bool $capitalize_first_character
	 *
	 * @return string
	 */
	public function camelcase_to_dashes( $string ) {
		return preg_replace_callback( '/\b([A-Z]+)\b/', function ( $word ) {
			return strtolower( $word[1] );
		}, $string );
	}

	/**
	 * Get plugin basename from plugin slug.
	 *
	 * @param string $plugin_slug
	 *
	 * @return string|null
	 */
	public function get_plugin_basename( $plugin_slug ) {
		$plugin_basenames = array_keys( get_plugins() );

		foreach ( $plugin_basenames as $plugin_basename ) {
			if ( preg_match( '|^' . $plugin_slug . '/|', $plugin_basename ) ) {
				return $plugin_basename;
			}
		}

		return null;
	}

	/**
	 * Check if plugin is active.
	 *
	 * @param string|array $plugin
	 *
	 * @return bool
	 *
	 * @deprecated 3.0
	 */
	public function is_plugin_active( $plugin ) {
		return kalium()->is->plugin_active( $plugin );
	}

	/**
	 * Resize dimensions by width.
	 *
	 * @param int $current_width
	 * @param int $current_height
	 * @param int $new_width
	 *
	 * @return array
	 */
	public function resize_by_width( $current_width, $current_height, $new_width ) {
		return [ $new_width, round( $new_width / $current_width * $current_height ) ];
	}

	/**
	 * Resize dimensions by height.
	 *
	 * @param int $current_width
	 * @param int $current_height
	 * @param int $new_height
	 *
	 * @return array
	 */
	public function resize_by_height( $current_width, $current_height, $new_height ) {
		return [ round( $new_height / $current_height * $current_width ), $new_height ];
	}

	/**
	 * Validate boolean value from string, number or bool.
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 * @uses wp_validate_boolean
	 */
	public function validate_boolean( $var ) {
		if ( is_string( $var ) ) {
			return wp_validate_boolean( $var ) || bool_from_yn( $var ) || 'yes' === $var;
		}

		return wp_validate_boolean( $var );
	}

	/**
	 * Get first array element value (or key).
	 *
	 * @param array $array
	 * @param bool $get_key
	 *
	 * @return mixed|null
	 */
	public function array_first( $array, $get_key = false ) {
		if ( is_array( $array ) && ! empty( $array ) ) {
			$keys = array_keys( $array );

			// Get array key
			if ( $get_key ) {
				return $keys[0];
			}

			// Get array value
			return $array[ $keys[0] ];
		}

		return null;
	}

	/**
	 * Escape JSON for use in HTML attributes.
	 *
	 * @param string $json
	 * @param bool $html
	 *
	 * @return string
	 *
	 * @since 3.0.4
	 */
	public function esc_json( $json, $html = false ) {
		return _wp_specialchars( $json, $html ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8', true );
	}
}
