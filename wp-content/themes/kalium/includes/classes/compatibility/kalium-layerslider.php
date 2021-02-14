<?php
/**
 * Kalium WordPress Theme
 *
 * Layer Slider compatibility class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_LayerSlider {

	/**
	 * Required plugin/s for this class.
	 *
	 * @var array
	 */
	public static $plugins = [
		'LayerSlider/layerslider.php',
	];

	/**
	 * Class instructor, define necessary actions.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'layerslider_ready', [ $this, '_disable_autoupdates' ] );
	}

	/**
	 * Disable auto updates.
	 *
	 * @return void
	 */
	public function _disable_autoupdates() {
		$GLOBALS['lsAutoUpdateBox'] = false;
	}
}