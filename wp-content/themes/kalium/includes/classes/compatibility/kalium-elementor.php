<?php
/**
 * Kalium WordPress Theme
 *
 * Elementor compatibility class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Elementor {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Elementor init
		add_action( 'elementor/init', [ $this, '_init' ] );

		// Register element categories
		add_action( 'elementor/elements/categories_registered', [ $this, '_register_categories' ] );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, '_register_widgets' ] );
	}

	/**
	 * Check if post ID is built with Elementor.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_built_with_elementor( $post_id ) {
		if ( ! kalium()->is->elementor_active() ) {
			return false;
		}

		return \Elementor\Plugin::instance()->db->is_built_with_elementor( $post_id );
	}

	/**
	 * Whether current request is the elementor preview iframe.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_preview( $post_id = 0 ) {
		if ( $post_id > 0 ) {
			return \Elementor\Plugin::instance()->preview->is_preview_mode( $post_id );
		}

		return \Elementor\Plugin::instance()->preview->is_preview();
	}

	/**
	 * Elementor init.
	 *
	 * @return void
	 */
	public function _init() {

		// Helpers class
		kalium()->require_file( 'includes/elementor/helpers.php' );

		// Add Controls classes
		kalium()->require_file( 'includes/elementor/control-sets/query-posts.php' );

		// Include widgets files
		$this->include_widgets_files();
	}

	/**
	 * Register element categories.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function _register_categories( $elements_manager ) {

		// Kalium Elements
		$elements_manager->add_category( 'kalium-elements', [
			'title' => 'Kalium Elements',
			'icon'  => 'eicon-theme-style',
		] );
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function _register_widgets( $widgets_manager ) {

		// Portfolio items widget
		$widgets_manager->register_widget_type( new \Kalium\Elementor\Widgets\Portfolio_Items_Legacy() );
	}

	/**
	 * Include widgets.
	 *
	 * @return void
	 */
	private function include_widgets_files() {
		kalium()->require_file( 'includes/elementor/widgets/portfolio-items-legacy/portfolio-items-legacy.php' );
	}
}
