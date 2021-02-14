<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium Demo Content Importer plugin.
 *
 * @version 2.0
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Importer {

	/**
	 * Instance of the class, because it is a singleton.
	 *
	 * @var self
	 * @static
	 */
	private static $instance;

	/**
	 * Create and/or return instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Init hooks
		add_action( 'admin_init', [ $this, '_admin_init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, '_admin_enqueue_scripts' ] );
		add_action( 'wp_ajax_kalium_demos_import_actions', [ $this, '_demo_import_actions' ] );
		add_action( 'wp_ajax_kalium_demos_import_content_pack', [ $this, 'import_content_pack_page' ] );
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function include_files() {
		include_once __DIR__ . '/includes/classes/kalium-demo-content-pack.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-task.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-helpers.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-backup-manager.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-manager.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-instance.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-wordpress-import.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-theme-options.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-revolution-slider.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-theme-custom-css.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-wordpress-widgets.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-typography.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-install-child-theme.php';
		include_once __DIR__ . '/includes/classes/kalium-demo-content-import-type-woocommerce-product-filter.php';
	}

	/**
	 * Admin init hook.
	 *
	 * @return void
	 */
	public function _admin_init() {

		// Includes
		$this->include_files();
	}

	/**
	 * Enqueue importer assets.
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {

		// Enqueue style and script on Demos page
		if ( 'kalium' === kalium()->request->query( 'page' ) && 'demos' === kalium()->request->query( 'tab' ) ) {
			kalium_enqueue( 'importer-css', kalium()->locate_file_url( 'includes/libraries/laborator/importer/assets/css/importer.min.css' ) );
			kalium_enqueue( 'importer-js', kalium()->locate_file_url( 'includes/libraries/laborator/importer/assets/js/importer.min.js' ) );
		}
	}

	/**
	 * AJAX gate for import actions of demo content importer.
	 *
	 * @return void
	 *
	 * @see Kalium_Demo_Import_Manager
	 * @see Kalium_Demo_Content_Pack
	 */
	public function _demo_import_actions() {

		// Only allowed users
		if ( current_user_can( 'install_plugins' ) ) {

			// Content pack ID
			$content_pack_id = kalium()->request->xhr_request( 'content-pack' );

			/**
			 * Import demo content actions.
			 *
			 * Hook: kalium_demo_content_import_actions
			 */
			do_action( 'kalium_demo_content_import_actions' );

			// Hooks for specific content_pack_id
			if ( $content_pack = $this->get_content_pack( $content_pack_id ) ) {

				// Create import manager instance
				$import_manager = Kalium_Demo_Import_Manager::get_instance( $content_pack );

				// Create backup manager instance
				$backup_manager = Kalium_Demo_Backup_Manager::get_instance( $content_pack );

				// Assign import manager and backup manager to content pack
				$content_pack->import_manager( $import_manager );
				$content_pack->backup_manager( $backup_manager );

				/**
				 * Content pack import actions.
				 *
				 * Hook: kalium_demo_content_import_{$content_pack_id}
				 *
				 * @param Kalium_Demo_Import_Manager $import_manager
				 * @param Kalium_Demo_Backup_Manager $backup_manager
				 */
				do_action( "kalium_demo_content_import_{$content_pack_id}", $import_manager, $backup_manager );
			}
		}
	}

	/**
	 * Wrap source download links with license parameters.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function source_url( $url ) {
		if ( kalium()->theme_license->get_license_key() ) {
			return sprintf( '%1$s?license_key=%2$s', $url, kalium()->theme_license->get_license_key() );
		}

		return $url;
	}

	/**
	 * Get demo content packs available for install.
	 *
	 * @return Kalium_Demo_Content_Pack[]
	 */
	public function get_content_packs() {

		// Registered Demo Content Packs
		return array_map( [ Kalium_Demo_Content_Pack::class, 'create_instance' ], [

			// Main
			[
				// Content pack ID
				'id'     => 'main',

				// Name
				'name'   => 'Main',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-main',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						],
					],

					// WooCommerce Products
					'products'      => [
						'name'     => 'Products',
						'type'     => 'wordpress-import',
						'src'      => 'products.xml',
						'requires' => [
							'woocommerce',
						],
						'checked'  => false,
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-darkslider.zip',
							'revslider-homepage.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/main/',
			],

			// Agency
			[
				// Content pack ID
				'id'     => 'agency',

				// Name
				'name'   => 'Agency',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-agency',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/agency/',
			],

			// Fashion
			[
				// Content pack ID
				'id'     => 'fashion',

				// Name
				'name'   => 'Fashion',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-fashion',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'js_composer',
							'woocommerce',
						],
						'options'  => [
							'post_types'  => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'product'    => 'Products',
							],
							'front_page'  => 'Home',
							'posts_page'  => 'News',
							'menus'       => [
								'main-menu' => 'Main Menu',
							],
							'woocommerce' => [

								// Pages
								'pages'                => [
									'shop'      => 'Shop',
									'cart'      => 'Cart',
									'checkout'  => 'Checkout',
									'myaccount' => 'Account',
								],

								// Thumbnails
								'thumbnails'           => [
									'cropping' => 'uncropped',
								],

								// Other options
								'options'              => [
									'woocommerce_catalog_columns' => 3,
									'woocommerce_catalog_rows'    => 4,
								],

								// Taxonomy data
								'taxonomy_data'        => '[{"taxonomy":"product_cat","slug":"women","name":"Women","thumbnail":"2016\/12\/women.png"},{"taxonomy":"product_cat","slug":"bottoms-women","name":"Bottoms"},{"taxonomy":"product_cat","slug":"dresses","name":"Dresses"},{"taxonomy":"product_cat","slug":"jackets-women","name":"Jackets"},{"taxonomy":"product_cat","slug":"tops","name":"Tops"},{"taxonomy":"product_cat","slug":"accessories","name":"Accessories","thumbnail":"2016\/12\/accessories-1.png"},{"taxonomy":"product_cat","slug":"men","name":"Men","thumbnail":"2016\/12\/men-3.png"},{"taxonomy":"product_cat","slug":"jackets","name":"Jackets"},{"taxonomy":"product_cat","slug":"sweaters","name":"Sweaters"},{"taxonomy":"product_cat","slug":"t-shirts","name":"T Shirts"},{"taxonomy":"product_cat","slug":"shoes","name":"Shoes"},{"taxonomy":"product_tag","slug":"army","name":"army"},{"taxonomy":"product_tag","slug":"backpack","name":"backpack"},{"taxonomy":"product_tag","slug":"backpacks","name":"backpacks"},{"taxonomy":"product_tag","slug":"bag","name":"bag"},{"taxonomy":"product_tag","slug":"bandana","name":"bandana"},{"taxonomy":"product_tag","slug":"basic","name":"basic"},{"taxonomy":"product_tag","slug":"beige","name":"beige"},{"taxonomy":"product_tag","slug":"belt","name":"belt"},{"taxonomy":"product_tag","slug":"black","name":"black"},{"taxonomy":"product_tag","slug":"blank","name":"blank"},{"taxonomy":"product_tag","slug":"blue","name":"blue"},{"taxonomy":"product_tag","slug":"bomber","name":"bomber"},{"taxonomy":"product_tag","slug":"boys","name":"boys"},{"taxonomy":"product_tag","slug":"broken","name":"broken"},{"taxonomy":"product_tag","slug":"brown","name":"brown"},{"taxonomy":"product_tag","slug":"bw","name":"bw"},{"taxonomy":"product_tag","slug":"camouflage","name":"camouflage"},{"taxonomy":"product_tag","slug":"casual","name":"casual"},{"taxonomy":"product_tag","slug":"classic","name":"classic"},{"taxonomy":"product_tag","slug":"classy","name":"classy"},{"taxonomy":"product_tag","slug":"cloth","name":"cloth"},{"taxonomy":"product_tag","slug":"coat","name":"coat"},{"taxonomy":"product_tag","slug":"cream","name":"cream"},{"taxonomy":"product_tag","slug":"creme","name":"creme"},{"taxonomy":"product_tag","slug":"cropped","name":"cropped"},{"taxonomy":"product_tag","slug":"dress","name":"dress"},{"taxonomy":"product_tag","slug":"emerald","name":"emerald"},{"taxonomy":"product_tag","slug":"fall","name":"fall"},{"taxonomy":"product_tag","slug":"frilled","name":"frilled"},{"taxonomy":"product_tag","slug":"frills","name":"frills"},{"taxonomy":"product_tag","slug":"gentlemen","name":"gentlemen"},{"taxonomy":"product_tag","slug":"glasses","name":"glasses"},{"taxonomy":"product_tag","slug":"glossy","name":"glossy"},{"taxonomy":"product_tag","slug":"gray","name":"gray"},{"taxonomy":"product_tag","slug":"green","name":"green"},{"taxonomy":"product_tag","slug":"guys","name":"guys"},{"taxonomy":"product_tag","slug":"hat","name":"hat"},{"taxonomy":"product_tag","slug":"heels","name":"heels"},{"taxonomy":"product_tag","slug":"herringbone","name":"herringbone"},{"taxonomy":"product_tag","slug":"high","name":"high"},{"taxonomy":"product_tag","slug":"jacket","name":"jacket"},{"taxonomy":"product_tag","slug":"jeans","name":"jeans"},{"taxonomy":"product_tag","slug":"lace","name":"lace"},{"taxonomy":"product_tag","slug":"leather","name":"leather"},{"taxonomy":"product_tag","slug":"low","name":"low"},{"taxonomy":"product_tag","slug":"mini","name":"mini"},{"taxonomy":"product_tag","slug":"navy","name":"navy"},{"taxonomy":"product_tag","slug":"ombre","name":"ombre"},{"taxonomy":"product_tag","slug":"openwork","name":"openwork"},{"taxonomy":"product_tag","slug":"oversized","name":"Oversized"},{"taxonomy":"product_tag","slug":"pocket","name":"pocket"},{"taxonomy":"product_tag","slug":"pompom","name":"pompom"},{"taxonomy":"product_tag","slug":"print","name":"print"},{"taxonomy":"product_tag","slug":"rain","name":"rain"},{"taxonomy":"product_tag","slug":"scarf","name":"scarf"},{"taxonomy":"product_tag","slug":"shirt","name":"shirt"},{"taxonomy":"product_tag","slug":"shoes","name":"shoes"},{"taxonomy":"product_tag","slug":"short","name":"short"},{"taxonomy":"product_tag","slug":"silver","name":"silver"},{"taxonomy":"product_tag","slug":"skirt","name":"skirt"},{"taxonomy":"product_tag","slug":"slippers","name":"slippers"},{"taxonomy":"product_tag","slug":"sneakers","name":"sneakers"},{"taxonomy":"product_tag","slug":"soft-touch","name":"soft touch"},{"taxonomy":"product_tag","slug":"stitch","name":"stitch"},{"taxonomy":"product_tag","slug":"striped","name":"striped"},{"taxonomy":"product_tag","slug":"studded","name":"studded"},{"taxonomy":"product_tag","slug":"studio","name":"studio"},{"taxonomy":"product_tag","slug":"sun","name":"sun"},{"taxonomy":"product_tag","slug":"sweater","name":"sweater"},{"taxonomy":"product_tag","slug":"sweatshirt","name":"sweatshirt"},{"taxonomy":"product_tag","slug":"t-shirt","name":"t shirt"},{"taxonomy":"product_tag","slug":"trench","name":"trench"},{"taxonomy":"product_tag","slug":"tricolor","name":"tricolor"},{"taxonomy":"product_tag","slug":"tshirt","name":"tshirt"},{"taxonomy":"product_tag","slug":"umbrella","name":"umbrella"},{"taxonomy":"product_tag","slug":"urban","name":"urban"},{"taxonomy":"product_tag","slug":"white","name":"white"},{"taxonomy":"product_tag","slug":"women","name":"women"},{"taxonomy":"product_tag","slug":"wraparound","name":"wraparound"},{"taxonomy":"product_tag","slug":"yellow","name":"yellow"},{"taxonomy":"product_tag","slug":"zara","name":"zara"},{"taxonomy":"pa_colours","slug":"brown","name":"Brown"},{"taxonomy":"pa_colours","slug":"burgundy","name":"Burgundy"},{"taxonomy":"pa_colours","slug":"purple","name":"Purple"},{"taxonomy":"pa_colours","slug":"turquoise","name":"Turquoise"},{"taxonomy":"pa_colours","slug":"blue","name":"Blue"},{"taxonomy":"pa_colours","slug":"white","name":"White"},{"taxonomy":"pa_colours","slug":"black","name":"Black"},{"taxonomy":"pa_colours","slug":"grey","name":"Grey"},{"taxonomy":"pa_colours","slug":"beige","name":"Beige"},{"taxonomy":"pa_colours","slug":"navy","name":"Navy"},{"taxonomy":"pa_colours","slug":"red","name":"Red"},{"taxonomy":"pa_colours","slug":"yellow","name":"Yellow"},{"taxonomy":"pa_material","slug":"leather","name":"Leather"},{"taxonomy":"pa_material","slug":"cotton","name":"Cotton"},{"taxonomy":"pa_material","slug":"wool","name":"Wool"},{"taxonomy":"pa_material","slug":"silk","name":"Silk"},{"taxonomy":"pa_size","slug":"s","name":"S"},{"taxonomy":"pa_size","slug":"m","name":"M"},{"taxonomy":"pa_size","slug":"l","name":"L"},{"taxonomy":"pa_size","slug":"xl","name":"XL"},{"taxonomy":"pa_size","slug":"xxl","name":"XXL"}]',

								// Attribute taxonomies
								'attribute_taxonomies' => '[{"attribute_name":"colours","attribute_label":"Colours"},{"attribute_name":"material","attribute_label":"Material"},{"attribute_name":"size","attribute_label":"Size"}]',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-homepage-full.zip',
							'revslider-homepage-slider.zip',
							'revslider-parallax-1.zip',
							'revslider-parallax-2.zip',
							'revslider-parallax-3.zip',
							'revslider-shop-accessories.zip',
							'revslider-shop-men.zip',
							'revslider-shop-shoes.zip',
							'revslider-shop-women.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":84,"name":"Footer Menu 1"},{"term_id":85,"name":"Footer menu 2"},{"term_id":86,"name":"Footer Menu 3"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/fashion/',
			],

			// Bookstore
			[
				// Content pack ID
				'id'     => 'bookstore',

				// Name
				'name'   => 'Bookstore',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-bookstore',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'woocommerce',
							'mailchimp-for-wp',
						],
						'options'  => [
							'post_types'  => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
								'product'    => 'Products',
							],
							'front_page'  => 'Home',
							'posts_page'  => 'Blog',
							'menus'       => [
								'main-menu' => 'Main Menu',
							],
							'woocommerce' => [

								// Pages
								'pages'                => [
									'shop'      => 'Books',
									'cart'      => 'Cart',
									'checkout'  => 'Checkout',
									'myaccount' => 'My account',
								],

								// Thumbnails
								'thumbnails'           => [
									'cropping'               => 'custom',
									'cropping_custom_width'  => 50,
									'cropping_custom_height' => 76,
									'image_width'            => 300,
								],

								// Other Options
								'options'              => [
									'woocommerce_shop_page_display'        => 'both',
									'woocommerce_category_archive_display' => 'both',
									'woocommerce_catalog_columns'          => 3,
									'woocommerce_catalog_rows'             => 4,
								],

								// Taxonomy data
								'taxonomy_data'        => '[{"taxonomy":"product_cat","slug":"uncategorized","name":"Uncategorized","thumbnail":"2019\/04\/uncategorized.png"},{"taxonomy":"product_cat","slug":"biography","name":"Biography","thumbnail":"2019\/04\/biography.png"},{"taxonomy":"product_cat","slug":"childrens-books","name":"Children\'s","thumbnail":"2019\/04\/children.png"},{"taxonomy":"product_cat","slug":"cooking","name":"Cooking","thumbnail":"2019\/04\/cook.png"},{"taxonomy":"product_cat","slug":"drama","name":"Drama","thumbnail":"2019\/04\/drama.png"},{"taxonomy":"product_cat","slug":"lifestyle","name":"Family","thumbnail":"2019\/04\/family.png"},{"taxonomy":"product_cat","slug":"fiction","name":"Fiction","thumbnail":"2019\/04\/fictive.png"},{"taxonomy":"product_cat","slug":"history","name":"History","thumbnail":"2019\/04\/history.png"},{"taxonomy":"product_cat","slug":"crime-thrillers-mystery","name":"Mystery","thumbnail":"2019\/04\/mystery.png"},{"taxonomy":"product_cat","slug":"politics","name":"Politics","thumbnail":"2019\/04\/policitcs.png"},{"taxonomy":"product_tag","slug":"education","name":"Education"},{"taxonomy":"pa_book-author","slug":"a-j-finn","name":"A. J. Finn"},{"taxonomy":"pa_book-author","slug":"annelies-marie-frank","name":"Anne Frank"},{"taxonomy":"pa_book-author","slug":"camille-pagan","name":"Camille Pag\u00e1n"},{"taxonomy":"pa_book-author","slug":"daniel-h-pink","name":"Daniel H. Pink"},{"taxonomy":"pa_book-author","slug":"danielle-steel","name":"Danielle Steel"},{"taxonomy":"pa_book-author","slug":"david-quammen","name":"David Quammen"},{"taxonomy":"pa_book-author","slug":"delia-owens","name":"Delia Owens"},{"taxonomy":"pa_book-author","slug":"dr-seuss","name":"Dr. Seuss"},{"taxonomy":"pa_book-author","slug":"elliot-ackerman","name":"Elliot Ackerman"},{"taxonomy":"pa_book-author","slug":"etaf-rum","name":"Etaf Rum"},{"taxonomy":"pa_book-author","slug":"heather-morris","name":"Heather Morris"},{"taxonomy":"pa_book-author","slug":"ismail-kadare","name":"Ismail Kadare"},{"taxonomy":"pa_book-author","slug":"james-holland","name":"James Holland"},{"taxonomy":"pa_book-author","slug":"joanna-gaines","name":"Joanna Gaines"},{"taxonomy":"pa_book-author","slug":"jordan-b-peterson","name":"Jordan B. Peterson"},{"taxonomy":"pa_book-author","slug":"katie-parla","name":"Katie Parla"},{"taxonomy":"pa_book-author","slug":"kirk-wallace-johnson","name":"Kirk Wallace Johnson"},{"taxonomy":"pa_book-author","slug":"kristin-fields","name":"Kristin Fields"},{"taxonomy":"pa_book-author","slug":"lisa-wingate","name":"Lisa Wingate"},{"taxonomy":"pa_book-author","slug":"mark-hyman-m-d","name":"Mark Hyman M.D."},{"taxonomy":"pa_book-author","slug":"mark-manson","name":"Mark Manson"},{"taxonomy":"pa_book-author","slug":"marlon-james","name":"Marlon James"},{"taxonomy":"pa_book-author","slug":"maya-angelou","name":"Maya Angelou"},{"taxonomy":"pa_book-author","slug":"michael-pollan","name":"Michael Pollan"},{"taxonomy":"pa_book-author","slug":"michael-wolff","name":"Michael Wolff"},{"taxonomy":"pa_book-author","slug":"michelle-obama","name":"Michelle Obama"},{"taxonomy":"pa_book-author","slug":"namwali-serpell","name":"Namwali Serpell"},{"taxonomy":"pa_book-author","slug":"ned-vizzini","name":"Ned Vizzini"},{"taxonomy":"pa_book-author","slug":"patrick-radden-keefe","name":"Patrick Radden Keefe"},{"taxonomy":"pa_book-author","slug":"peter-heller","name":"Peter Heller"},{"taxonomy":"pa_book-author","slug":"r-j-palacio","name":"R. J. Palacio"},{"taxonomy":"pa_book-author","slug":"rachel-hollis","name":"Rachel Hollis"},{"taxonomy":"pa_book-author","slug":"ralph-ellison","name":"Ralph Ellison"},{"taxonomy":"pa_book-author","slug":"sigrid-nunez","name":"Sigrid Nunez"},{"taxonomy":"pa_book-author","slug":"stephen-hawking","name":"Stephen Hawking"},{"taxonomy":"pa_book-author","slug":"susan-bernhard","name":"Susan Bernhard"},{"taxonomy":"pa_book-author","slug":"tara-westover","name":"Tara Westover"},{"taxonomy":"pa_book-author","slug":"tomi-adeyemi","name":"Tomi Adeyemi"},{"taxonomy":"pa_book-author","slug":"trevor-noah","name":"Trevor Noah"},{"taxonomy":"pa_format","slug":"audio-cd","name":"Audio CD"},{"taxonomy":"pa_format","slug":"audiobook","name":"Audiobook"},{"taxonomy":"pa_format","slug":"hardcover","name":"Hardcover"},{"taxonomy":"pa_format","slug":"kindle-books","name":"Kindle Books"},{"taxonomy":"pa_format","slug":"paperback","name":"Paperback"},{"taxonomy":"pa_language","slug":"english","name":"English"},{"taxonomy":"pa_language","slug":"french","name":"French"},{"taxonomy":"pa_language","slug":"german","name":"German"},{"taxonomy":"pa_language","slug":"japanese","name":"Japanese"},{"taxonomy":"pa_language","slug":"spanish","name":"Spanish"},{"taxonomy":"pa_pages","slug":"192","name":"192"},{"taxonomy":"pa_pages","slug":"208","name":"208"},{"taxonomy":"pa_pages","slug":"212","name":"212"},{"taxonomy":"pa_pages","slug":"251","name":"251"},{"taxonomy":"pa_pages","slug":"256","name":"256"},{"taxonomy":"pa_pages","slug":"264","name":"264"},{"taxonomy":"pa_pages","slug":"270","name":"270"},{"taxonomy":"pa_pages","slug":"272","name":"272"},{"taxonomy":"pa_pages","slug":"283","name":"283"},{"taxonomy":"pa_pages","slug":"288","name":"288"},{"taxonomy":"pa_pages","slug":"304","name":"304"},{"taxonomy":"pa_pages","slug":"316","name":"316"},{"taxonomy":"pa_pages","slug":"321","name":"321"},{"taxonomy":"pa_pages","slug":"350","name":"350"},{"taxonomy":"pa_pages","slug":"352","name":"352"},{"taxonomy":"pa_pages","slug":"400","name":"400"},{"taxonomy":"pa_pages","slug":"409","name":"409"},{"taxonomy":"pa_pages","slug":"448","name":"448"},{"taxonomy":"pa_pages","slug":"464","name":"464"},{"taxonomy":"pa_pages","slug":"480","name":"480"},{"taxonomy":"pa_pages","slug":"544","name":"544"},{"taxonomy":"pa_pages","slug":"576","name":"576"},{"taxonomy":"pa_pages","slug":"640","name":"640"},{"taxonomy":"pa_publisher","slug":"atlantic-monthly-press","name":"Atlantic Monthly Press"},{"taxonomy":"pa_publisher","slug":"ballantine-books","name":"Ballantine Books"},{"taxonomy":"pa_publisher","slug":"bantam","name":"Bantam"},{"taxonomy":"pa_publisher","slug":"clarkson-potter","name":"Clarkson Potter"},{"taxonomy":"pa_publisher","slug":"disney-hyperion","name":"Disney-Hyperion"},{"taxonomy":"pa_publisher","slug":"doubleday","name":"Doubleday"},{"taxonomy":"pa_publisher","slug":"harper","name":"Harper"},{"taxonomy":"pa_publisher","slug":"harper-paperbacks","name":"Harper Paperbacks"},{"taxonomy":"pa_publisher","slug":"henry-holt-and-co-2","name":"Henry Holt and Co"},{"taxonomy":"pa_publisher","slug":"henry-holt-and-co","name":"Henry Holt and Co."},{"taxonomy":"pa_publisher","slug":"hogarth","name":"Hogarth"},{"taxonomy":"pa_publisher","slug":"knopf","name":"Knopf"},{"taxonomy":"pa_publisher","slug":"lake-union-publishing","name":"Lake Union Publishing"},{"taxonomy":"pa_publisher","slug":"little-a","name":"Little A"},{"taxonomy":"pa_publisher","slug":"little-brown-spark","name":"Little, Brown Spark"},{"taxonomy":"pa_publisher","slug":"modern-library","name":"Modern Library"},{"taxonomy":"pa_publisher","slug":"penguin-press","name":"Penguin Press"},{"taxonomy":"pa_publisher","slug":"quercus","name":"Quercus"},{"taxonomy":"pa_publisher","slug":"random-house","name":"Random House"},{"taxonomy":"pa_publisher","slug":"riverhead-books","name":"Riverhead Books"},{"taxonomy":"pa_publisher","slug":"simon-schuster","name":"Simon &amp; Schuster"},{"taxonomy":"pa_publisher","slug":"spiegel-grau","name":"Spiegel &amp; Grau"},{"taxonomy":"pa_publisher","slug":"thomas-nelson","name":"Thomas Nelson"},{"taxonomy":"pa_publisher","slug":"viking","name":"Viking"},{"taxonomy":"pa_publisher","slug":"william-morrow-cookbooks","name":"William Morrow Cookbooks"},{"taxonomy":"pa_publisher","slug":"windmill-books","name":"Windmill Books"},{"taxonomy":"pa_year-published","slug":"1993","name":"1993"},{"taxonomy":"pa_year-published","slug":"1994","name":"1994"},{"taxonomy":"pa_year-published","slug":"1998","name":"1998"},{"taxonomy":"pa_year-published","slug":"2007","name":"2007"},{"taxonomy":"pa_year-published","slug":"2009","name":"2009"},{"taxonomy":"pa_year-published","slug":"2014","name":"2014"},{"taxonomy":"pa_year-published","slug":"2016","name":"2016"},{"taxonomy":"pa_year-published","slug":"2018","name":"2018"},{"taxonomy":"pa_year-published","slug":"2019","name":"2019"}]',

								// Attribute taxonomies
								'attribute_taxonomies' => '[{"attribute_name":"book-author","attribute_label":"Author"},{"attribute_name":"format","attribute_label":"Format"},{"attribute_name":"language","attribute_label":"Language"},{"attribute_name":"pages","attribute_label":"Pages"},{"attribute_name":"publisher","attribute_label":"Publisher"},{"attribute_name":"year-published","attribute_label":"Year Published"}]',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-bookstore.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":130,"name":"Footer Menu 1"},{"term_id":131,"name":"Footer Menu 2"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/bookstore/',
			],

			// Wedding
			[
				// Content pack ID
				'id'     => 'wedding',

				// Name
				'name'   => 'Wedding',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-wedding',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'ninja-forms',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
							],
							'front_page' => 'Homepage',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-intro.zip',
						],
						'requires' => 'revslider',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/wedding/',
			],

			// Medical
			[
				// Content pack ID
				'id'     => 'medical',

				// Name
				'name'   => 'Medical',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-medical',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
							'ninja-forms',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-hospital-slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":2,"name":"Footer Departments 1"},{"term_id":3,"name":"Footer Departments 2"},{"term_id":4,"name":"Footer Links"},{"term_id":37,"name":"Departments Menu"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/medical/',
			],

			// Automotive
			[
				// Content pack ID
				'id'     => 'automotive',

				// Name
				'name'   => 'Automotive',

				// Import data
				'import' => [

					// Child theme
					'child-theme'    => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-automotive',
						],
					],

					// WordPress Content XML
					'content'        => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'woocommerce',
						],
						'options'  => [
							'post_types'  => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'product'    => 'Products',
							],
							'front_page'  => 'Home',
							'posts_page'  => 'News',
							'menus'       => [
								'main-menu' => 'Main Menu',
							],
							'woocommerce' => [

								// Pages
								'pages'                => [
									'shop'      => 'Cars',
									'cart'      => 'Cart',
									'checkout'  => 'Checkout',
									'myaccount' => 'My account',
								],

								// Thumbnails
								'thumbnails'           => [
									'cropping'               => 'custom',
									'cropping_custom_width'  => 5,
									'cropping_custom_height' => 3,
									'image_width'            => 600,
								],

								// Other Options
								'options'              => [
									'woocommerce_category_archive_display' => 'both',
									'woocommerce_catalog_columns'          => 3,
									'woocommerce_catalog_rows'             => 8,
								],

								// Taxonomy data
								'taxonomy_data'        => '[{"taxonomy":"product_cat","slug":"hybrid","name":"Hybrid-Electric"},{"taxonomy":"product_cat","slug":"sport","name":"Sport"},{"taxonomy":"product_cat","slug":"small","name":"Small"},{"taxonomy":"product_cat","slug":"truck","name":"Truck"},{"taxonomy":"product_cat","slug":"motorcycle","name":"Motorcycle"},{"taxonomy":"product_cat","slug":"hatchback","name":"Hatchback"},{"taxonomy":"product_cat","slug":"sedan","name":"Sedan"},{"taxonomy":"product_cat","slug":"convertible","name":"Convertible"},{"taxonomy":"product_cat","slug":"estate","name":"Estate"},{"taxonomy":"product_cat","slug":"suv","name":"SUV"},{"taxonomy":"product_cat","slug":"van","name":"Van"},{"taxonomy":"product_tag","slug":"suv-award","name":"SUV Award"},{"taxonomy":"product_tag","slug":"bmw","name":"bmw"},{"taxonomy":"product_tag","slug":"4er","name":"4er"},{"taxonomy":"product_tag","slug":"convertible","name":"convertible"},{"taxonomy":"pa_brand","slug":"alfa-romeo","name":"Alfa Romeo"},{"taxonomy":"pa_brand","slug":"audi","name":"Audi"},{"taxonomy":"pa_brand","slug":"bmw","name":"BMW"},{"taxonomy":"pa_brand","slug":"citroen","name":"Citro\u00ebn"},{"taxonomy":"pa_brand","slug":"fiat","name":"Fiat"},{"taxonomy":"pa_brand","slug":"ford","name":"Ford"},{"taxonomy":"pa_brand","slug":"honda","name":"Honda"},{"taxonomy":"pa_brand","slug":"hyundai","name":"Hyundai"},{"taxonomy":"pa_brand","slug":"infiniti","name":"Infiniti"},{"taxonomy":"pa_brand","slug":"jaguar","name":"Jaguar"},{"taxonomy":"pa_brand","slug":"jeep","name":"Jeep"},{"taxonomy":"pa_brand","slug":"land-rover","name":"Land Rover"},{"taxonomy":"pa_brand","slug":"mazda","name":"Mazda"},{"taxonomy":"pa_brand","slug":"mercedes-benz","name":"Mercedes Benz"},{"taxonomy":"pa_brand","slug":"mini","name":"Mini"},{"taxonomy":"pa_brand","slug":"mitsubishi","name":"Mitsubishi"},{"taxonomy":"pa_brand","slug":"nissan","name":"Nissan"},{"taxonomy":"pa_brand","slug":"peugeot","name":"Peugeot"},{"taxonomy":"pa_brand","slug":"porsche","name":"Porsche"},{"taxonomy":"pa_brand","slug":"renault","name":"Renault"},{"taxonomy":"pa_brand","slug":"seat","name":"Seat"},{"taxonomy":"pa_brand","slug":"skoda","name":"Skoda"},{"taxonomy":"pa_brand","slug":"smart","name":"Smart"},{"taxonomy":"pa_brand","slug":"suzuku","name":"Suzuku"},{"taxonomy":"pa_brand","slug":"tesla","name":"Tesla"},{"taxonomy":"pa_brand","slug":"toyota","name":"Toyota"},{"taxonomy":"pa_brand","slug":"vauxhall","name":"Vauxhall"},{"taxonomy":"pa_brand","slug":"vespa","name":"Vespa"},{"taxonomy":"pa_brand","slug":"volkswagen","name":"Volkswagen"},{"taxonomy":"pa_brand","slug":"volvo","name":"Volvo"},{"taxonomy":"pa_color","slug":"beige","name":"Beige"},{"taxonomy":"pa_color","slug":"black","name":"Black"},{"taxonomy":"pa_color","slug":"blue","name":"Blue"},{"taxonomy":"pa_color","slug":"brown","name":"Brown"},{"taxonomy":"pa_color","slug":"green","name":"Green"},{"taxonomy":"pa_color","slug":"metallic","name":"Metallic"},{"taxonomy":"pa_color","slug":"orange","name":"Orange"},{"taxonomy":"pa_color","slug":"purple","name":"Purple"},{"taxonomy":"pa_color","slug":"red","name":"Red"},{"taxonomy":"pa_color","slug":"silver","name":"Silver"},{"taxonomy":"pa_color","slug":"white","name":"White"},{"taxonomy":"pa_color","slug":"yellow","name":"Yellow"},{"taxonomy":"pa_doors","slug":"23","name":"2\/3"},{"taxonomy":"pa_doors","slug":"45","name":"4\/5"},{"taxonomy":"pa_doors","slug":"67","name":"6\/7"},{"taxonomy":"pa_fuel","slug":"diesel","name":"Diesel"},{"taxonomy":"pa_fuel","slug":"electric","name":"Electric"},{"taxonomy":"pa_fuel","slug":"hybrid","name":"Hybrid"},{"taxonomy":"pa_fuel","slug":"petrol","name":"Petrol"},{"taxonomy":"pa_horsepower","slug":"101","name":"101"},{"taxonomy":"pa_horsepower","slug":"105","name":"105"},{"taxonomy":"pa_horsepower","slug":"110","name":"110"},{"taxonomy":"pa_horsepower","slug":"120","name":"120"},{"taxonomy":"pa_horsepower","slug":"125","name":"125"},{"taxonomy":"pa_horsepower","slug":"140","name":"140"},{"taxonomy":"pa_horsepower","slug":"150","name":"150"},{"taxonomy":"pa_horsepower","slug":"160","name":"160"},{"taxonomy":"pa_horsepower","slug":"170","name":"170"},{"taxonomy":"pa_horsepower","slug":"172","name":"172"},{"taxonomy":"pa_horsepower","slug":"180","name":"180"},{"taxonomy":"pa_horsepower","slug":"185","name":"185"},{"taxonomy":"pa_horsepower","slug":"190","name":"190"},{"taxonomy":"pa_horsepower","slug":"220","name":"220"},{"taxonomy":"pa_horsepower","slug":"230","name":"230"},{"taxonomy":"pa_horsepower","slug":"235","name":"235"},{"taxonomy":"pa_horsepower","slug":"240","name":"240"},{"taxonomy":"pa_horsepower","slug":"243","name":"243"},{"taxonomy":"pa_horsepower","slug":"255","name":"255"},{"taxonomy":"pa_horsepower","slug":"300","name":"300"},{"taxonomy":"pa_horsepower","slug":"310","name":"310"},{"taxonomy":"pa_horsepower","slug":"350","name":"350"},{"taxonomy":"pa_horsepower","slug":"360","name":"360"},{"taxonomy":"pa_horsepower","slug":"380","name":"380"},{"taxonomy":"pa_horsepower","slug":"385","name":"385"},{"taxonomy":"pa_horsepower","slug":"580","name":"580"},{"taxonomy":"pa_horsepower","slug":"89","name":"89"},{"taxonomy":"pa_horsepower","slug":"90","name":"90"},{"taxonomy":"pa_interior-features","slug":"auxiliary-heating","name":"Auxiliary heating"},{"taxonomy":"pa_interior-features","slug":"bluetooth","name":"Bluetooth"},{"taxonomy":"pa_interior-features","slug":"cd-player","name":"CD player"},{"taxonomy":"pa_interior-features","slug":"central-locking","name":"Central locking"},{"taxonomy":"pa_interior-features","slug":"cruise-control","name":"Cruise control"},{"taxonomy":"pa_interior-features","slug":"electric-heated-seats","name":"Electric heated seats"},{"taxonomy":"pa_interior-features","slug":"electric-seat-adjustment","name":"Electric seat adjustment"},{"taxonomy":"pa_interior-features","slug":"electric-side-mirror","name":"Electric side mirror"},{"taxonomy":"pa_interior-features","slug":"electric-windows","name":"Electric windows"},{"taxonomy":"pa_interior-features","slug":"hands-free-kit","name":"Hands-free kit"},{"taxonomy":"pa_interior-features","slug":"head-up-display","name":"Head-up display"},{"taxonomy":"pa_interior-features","slug":"isofix","name":"Isofix"},{"taxonomy":"pa_interior-features","slug":"mp3-interface","name":"MP3 interface"},{"taxonomy":"pa_interior-features","slug":"multifunction-steering-wheel","name":"Multifunction steering wheel"},{"taxonomy":"pa_interior-features","slug":"navigation-system","name":"Navigation system"},{"taxonomy":"pa_interior-features","slug":"on-board-computer","name":"On-board computer"},{"taxonomy":"pa_interior-features","slug":"power-assisted-steering","name":"Power Assisted Steering"},{"taxonomy":"pa_interior-features","slug":"rain-sensor","name":"Rain sensor"},{"taxonomy":"pa_interior-features","slug":"ski-bag","name":"Ski bag"},{"taxonomy":"pa_interior-features","slug":"start-stop-system","name":"Start-stop system"},{"taxonomy":"pa_interior-features","slug":"sunroof","name":"Sunroof"},{"taxonomy":"pa_interior-features","slug":"tunerradio","name":"Tuner\/radio"},{"taxonomy":"pa_interior-features","slug":"ventilated-seats","name":"Ventilated Seats"},{"taxonomy":"pa_kilometers","slug":"0","name":"0"},{"taxonomy":"pa_kilometers","slug":"10000","name":"10000"},{"taxonomy":"pa_kilometers","slug":"1200","name":"1200"},{"taxonomy":"pa_kilometers","slug":"12000","name":"12000"},{"taxonomy":"pa_kilometers","slug":"20000","name":"20000"},{"taxonomy":"pa_kilometers","slug":"25000","name":"25000"},{"taxonomy":"pa_kilometers","slug":"5000","name":"5000"},{"taxonomy":"pa_kilometers","slug":"50000","name":"50000"},{"taxonomy":"pa_kilometers","slug":"7800","name":"7800"},{"taxonomy":"pa_security","slug":"abs","name":"ABS"},{"taxonomy":"pa_security","slug":"adaptive-cruise-control","name":"Adaptive Cruise Control"},{"taxonomy":"pa_security","slug":"adaptive-lighting","name":"Adaptive lighting"},{"taxonomy":"pa_security","slug":"blind-spot-monitor","name":"Blind Spot Monitor"},{"taxonomy":"pa_security","slug":"collision-avoidance-system","name":"Collision Avoidance System"},{"taxonomy":"pa_security","slug":"daytime-running-lights","name":"Daytime running lights"},{"taxonomy":"pa_security","slug":"esp","name":"ESP"},{"taxonomy":"pa_security","slug":"fog-lamp","name":"Fog lamp"},{"taxonomy":"pa_security","slug":"immobilizer","name":"Immobilizer"},{"taxonomy":"pa_security","slug":"keyless-entry","name":"Keyless Entry"},{"taxonomy":"pa_security","slug":"lane-departure-warning","name":"Lane Departure Warning"},{"taxonomy":"pa_security","slug":"led-headlights","name":"LED Headlights"},{"taxonomy":"pa_security","slug":"light-sensor","name":"Light sensor"},{"taxonomy":"pa_security","slug":"traction-control","name":"Traction control"},{"taxonomy":"pa_security","slug":"xenon-headlights","name":"Xenon headlights"},{"taxonomy":"pa_sensors","slug":"camera","name":"Camera"},{"taxonomy":"pa_sensors","slug":"front-sensors","name":"Front sensors"},{"taxonomy":"pa_sensors","slug":"rear-sensors","name":"Rear sensors"},{"taxonomy":"pa_sensors","slug":"self-steering-systems","name":"Self-steering systems"},{"taxonomy":"pa_system","slug":"awd-all-wheel-drive","name":"AWD (All Wheel Drive)"},{"taxonomy":"pa_system","slug":"fwd-fear-wheel-drive","name":"FWD (Fear Wheel Drive)"},{"taxonomy":"pa_system","slug":"rwd-rear-wheel-drive","name":"RWD (Rear Wheel Drive)"},{"taxonomy":"pa_transmission","slug":"automatic","name":"Automatic"},{"taxonomy":"pa_transmission","slug":"manual","name":"Manual"},{"taxonomy":"pa_transmission","slug":"semi-automatic","name":"Semi-automatic"}]',

								// Attribute taxonomies
								'attribute_taxonomies' => '[{"attribute_name":"brand","attribute_label":"Brand"},{"attribute_name":"color","attribute_label":"Color"},{"attribute_name":"doors","attribute_label":"Doors"},{"attribute_name":"fuel","attribute_label":"Fuel"},{"attribute_name":"horsepower","attribute_label":"Horsepower"},{"attribute_name":"interior-features","attribute_label":"Interior features"},{"attribute_name":"kilometers","attribute_label":"Kilometers"},{"attribute_name":"security","attribute_label":"Security"},{"attribute_name":"sensors","attribute_label":"Sensors"},{"attribute_name":"system","attribute_label":"Drivetrain"},{"attribute_name":"transmission","attribute_label":"Transmission"}]',
							],
						],
					],

					// Theme Options
					'theme-options'  => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Product Filter
					'product-filter' => [
						'type'     => 'woocommerce-prdctfltr',
						'src'      => 'prdctfltr.json',
						'requires' => 'prdctfltr',
						'options'  => [

							// Product taxonomy attributes
							'product_attributes' => '[{"tax":"product_cat","id":234,"name":"Motorcycle","slug":"motorcycle"},{"tax":"product_cat","id":143,"name":"Small","slug":"small"},{"tax":"product_cat","id":16,"name":"Hatchback","slug":"hatchback"},{"tax":"product_cat","id":131,"name":"Convertible","slug":"convertible"},{"tax":"product_cat","id":142,"name":"Sport","slug":"sport"},{"tax":"product_cat","id":17,"name":"Sedan","slug":"sedan"},{"tax":"product_cat","id":19,"name":"Estate","slug":"estate"},{"tax":"product_cat","id":18,"name":"SUV","slug":"suv"},{"tax":"product_cat","id":21,"name":"Van","slug":"van"},{"tax":"product_cat","id":161,"name":"Truck","slug":"truck"},{"tax":"product_cat","id":141,"name":"Hybrid-Electric","slug":"hybrid"},{"tax":"product_tag","id":226,"name":"SUV Award","slug":"suv-award"},{"tax":"product_tag","id":229,"name":"bmw","slug":"bmw"},{"tax":"product_tag","id":230,"name":"4er","slug":"4er"},{"tax":"product_tag","id":232,"name":"convertible","slug":"convertible"},{"tax":"pa_brand","id":156,"name":"Alfa Romeo","slug":"alfa-romeo"},{"tax":"pa_brand","id":114,"name":"Audi","slug":"audi"},{"tax":"pa_brand","id":115,"name":"BMW","slug":"bmw"},{"tax":"pa_brand","id":128,"name":"Citro\u00ebn","slug":"citroen"},{"tax":"pa_brand","id":123,"name":"Fiat","slug":"fiat"},{"tax":"pa_brand","id":122,"name":"Ford","slug":"ford"},{"tax":"pa_brand","id":154,"name":"Honda","slug":"honda"},{"tax":"pa_brand","id":155,"name":"Hyundai","slug":"hyundai"},{"tax":"pa_brand","id":153,"name":"Infiniti","slug":"infiniti"},{"tax":"pa_brand","id":119,"name":"Jaguar","slug":"jaguar"},{"tax":"pa_brand","id":147,"name":"Jeep","slug":"jeep"},{"tax":"pa_brand","id":118,"name":"Land Rover","slug":"land-rover"},{"tax":"pa_brand","id":121,"name":"Mazda","slug":"mazda"},{"tax":"pa_brand","id":116,"name":"Mercedes Benz","slug":"mercedes-benz"},{"tax":"pa_brand","id":152,"name":"Mini","slug":"mini"},{"tax":"pa_brand","id":146,"name":"Mitsubishi","slug":"mitsubishi"},{"tax":"pa_brand","id":124,"name":"Nissan","slug":"nissan"},{"tax":"pa_brand","id":127,"name":"Peugeot","slug":"peugeot"},{"tax":"pa_brand","id":148,"name":"Porsche","slug":"porsche"},{"tax":"pa_brand","id":126,"name":"Renault","slug":"renault"},{"tax":"pa_brand","id":145,"name":"Seat","slug":"seat"},{"tax":"pa_brand","id":151,"name":"Skoda","slug":"skoda"},{"tax":"pa_brand","id":150,"name":"Smart","slug":"smart"},{"tax":"pa_brand","id":144,"name":"Suzuku","slug":"suzuku"},{"tax":"pa_brand","id":149,"name":"Tesla","slug":"tesla"},{"tax":"pa_brand","id":120,"name":"Toyota","slug":"toyota"},{"tax":"pa_brand","id":125,"name":"Vauxhall","slug":"vauxhall"},{"tax":"pa_brand","id":235,"name":"Vespa","slug":"vespa"},{"tax":"pa_brand","id":117,"name":"Volkswagen","slug":"volkswagen"},{"tax":"pa_brand","id":129,"name":"Volvo","slug":"volvo"},{"tax":"pa_color","id":34,"name":"Beige","slug":"beige"},{"tax":"pa_color","id":22,"name":"Black","slug":"black"},{"tax":"pa_color","id":24,"name":"Blue","slug":"blue"},{"tax":"pa_color","id":41,"name":"Brown","slug":"brown"},{"tax":"pa_color","id":37,"name":"Green","slug":"green"},{"tax":"pa_color","id":25,"name":"Metallic","slug":"metallic"},{"tax":"pa_color","id":36,"name":"Orange","slug":"orange"},{"tax":"pa_color","id":38,"name":"Purple","slug":"purple"},{"tax":"pa_color","id":23,"name":"Red","slug":"red"},{"tax":"pa_color","id":35,"name":"Silver","slug":"silver"},{"tax":"pa_color","id":40,"name":"White","slug":"white"},{"tax":"pa_color","id":39,"name":"Yellow","slug":"yellow"},{"tax":"pa_doors","id":109,"name":"2\/3","slug":"23"},{"tax":"pa_doors","id":110,"name":"4\/5","slug":"45"},{"tax":"pa_doors","id":111,"name":"6\/7","slug":"67"},{"tax":"pa_fuel","id":27,"name":"Diesel","slug":"diesel"},{"tax":"pa_fuel","id":30,"name":"Electric","slug":"electric"},{"tax":"pa_fuel","id":29,"name":"Hybrid","slug":"hybrid"},{"tax":"pa_fuel","id":28,"name":"Petrol","slug":"petrol"},{"tax":"pa_horsepower","id":183,"name":"101","slug":"101"},{"tax":"pa_horsepower","id":178,"name":"105","slug":"105"},{"tax":"pa_horsepower","id":133,"name":"110","slug":"110"},{"tax":"pa_horsepower","id":168,"name":"120","slug":"120"},{"tax":"pa_horsepower","id":187,"name":"125","slug":"125"},{"tax":"pa_horsepower","id":170,"name":"140","slug":"140"},{"tax":"pa_horsepower","id":177,"name":"150","slug":"150"},{"tax":"pa_horsepower","id":167,"name":"160","slug":"160"},{"tax":"pa_horsepower","id":165,"name":"170","slug":"170"},{"tax":"pa_horsepower","id":175,"name":"172","slug":"172"},{"tax":"pa_horsepower","id":164,"name":"180","slug":"180"},{"tax":"pa_horsepower","id":132,"name":"185","slug":"185"},{"tax":"pa_horsepower","id":139,"name":"190","slug":"190"},{"tax":"pa_horsepower","id":112,"name":"220","slug":"220"},{"tax":"pa_horsepower","id":157,"name":"230","slug":"230"},{"tax":"pa_horsepower","id":137,"name":"235","slug":"235"},{"tax":"pa_horsepower","id":182,"name":"240","slug":"240"},{"tax":"pa_horsepower","id":159,"name":"243","slug":"243"},{"tax":"pa_horsepower","id":162,"name":"255","slug":"255"},{"tax":"pa_horsepower","id":172,"name":"300","slug":"300"},{"tax":"pa_horsepower","id":171,"name":"310","slug":"310"},{"tax":"pa_horsepower","id":169,"name":"350","slug":"350"},{"tax":"pa_horsepower","id":176,"name":"360","slug":"360"},{"tax":"pa_horsepower","id":174,"name":"380","slug":"380"},{"tax":"pa_horsepower","id":181,"name":"385","slug":"385"},{"tax":"pa_horsepower","id":160,"name":"580","slug":"580"},{"tax":"pa_horsepower","id":186,"name":"89","slug":"89"},{"tax":"pa_horsepower","id":184,"name":"90","slug":"90"},{"tax":"pa_interior-features","id":65,"name":"Auxiliary heating","slug":"auxiliary-heating"},{"tax":"pa_interior-features","id":66,"name":"Bluetooth","slug":"bluetooth"},{"tax":"pa_interior-features","id":64,"name":"CD player","slug":"cd-player"},{"tax":"pa_interior-features","id":67,"name":"Central locking","slug":"central-locking"},{"tax":"pa_interior-features","id":68,"name":"Cruise control","slug":"cruise-control"},{"tax":"pa_interior-features","id":69,"name":"Electric heated seats","slug":"electric-heated-seats"},{"tax":"pa_interior-features","id":70,"name":"Electric seat adjustment","slug":"electric-seat-adjustment"},{"tax":"pa_interior-features","id":71,"name":"Electric side mirror","slug":"electric-side-mirror"},{"tax":"pa_interior-features","id":72,"name":"Electric windows","slug":"electric-windows"},{"tax":"pa_interior-features","id":73,"name":"Hands-free kit","slug":"hands-free-kit"},{"tax":"pa_interior-features","id":74,"name":"Head-up display","slug":"head-up-display"},{"tax":"pa_interior-features","id":75,"name":"Isofix","slug":"isofix"},{"tax":"pa_interior-features","id":79,"name":"MP3 interface","slug":"mp3-interface"},{"tax":"pa_interior-features","id":78,"name":"Multifunction steering wheel","slug":"multifunction-steering-wheel"},{"tax":"pa_interior-features","id":77,"name":"Navigation system","slug":"navigation-system"},{"tax":"pa_interior-features","id":76,"name":"On-board computer","slug":"on-board-computer"},{"tax":"pa_interior-features","id":80,"name":"Power Assisted Steering","slug":"power-assisted-steering"},{"tax":"pa_interior-features","id":81,"name":"Rain sensor","slug":"rain-sensor"},{"tax":"pa_interior-features","id":82,"name":"Ski bag","slug":"ski-bag"},{"tax":"pa_interior-features","id":83,"name":"Start-stop system","slug":"start-stop-system"},{"tax":"pa_interior-features","id":86,"name":"Sunroof","slug":"sunroof"},{"tax":"pa_interior-features","id":85,"name":"Tuner\/radio","slug":"tunerradio"},{"tax":"pa_interior-features","id":84,"name":"Ventilated Seats","slug":"ventilated-seats"},{"tax":"pa_kilometers","id":140,"name":"0","slug":"0"},{"tax":"pa_kilometers","id":180,"name":"10000","slug":"10000"},{"tax":"pa_kilometers","id":185,"name":"1200","slug":"1200"},{"tax":"pa_kilometers","id":158,"name":"12000","slug":"12000"},{"tax":"pa_kilometers","id":173,"name":"20000","slug":"20000"},{"tax":"pa_kilometers","id":166,"name":"25000","slug":"25000"},{"tax":"pa_kilometers","id":136,"name":"5000","slug":"5000"},{"tax":"pa_kilometers","id":179,"name":"50000","slug":"50000"},{"tax":"pa_kilometers","id":163,"name":"7800","slug":"7800"},{"tax":"pa_security","id":87,"name":"ABS","slug":"abs"},{"tax":"pa_security","id":88,"name":"Adaptive Cruise Control","slug":"adaptive-cruise-control"},{"tax":"pa_security","id":89,"name":"Adaptive lighting","slug":"adaptive-lighting"},{"tax":"pa_security","id":90,"name":"Blind Spot Monitor","slug":"blind-spot-monitor"},{"tax":"pa_security","id":91,"name":"Collision Avoidance System","slug":"collision-avoidance-system"},{"tax":"pa_security","id":92,"name":"Daytime running lights","slug":"daytime-running-lights"},{"tax":"pa_security","id":93,"name":"ESP","slug":"esp"},{"tax":"pa_security","id":94,"name":"Fog lamp","slug":"fog-lamp"},{"tax":"pa_security","id":95,"name":"Immobilizer","slug":"immobilizer"},{"tax":"pa_security","id":96,"name":"Keyless Entry","slug":"keyless-entry"},{"tax":"pa_security","id":97,"name":"Lane Departure Warning","slug":"lane-departure-warning"},{"tax":"pa_security","id":98,"name":"LED Headlights","slug":"led-headlights"},{"tax":"pa_security","id":99,"name":"Light sensor","slug":"light-sensor"},{"tax":"pa_security","id":100,"name":"Traction control","slug":"traction-control"},{"tax":"pa_security","id":101,"name":"Xenon headlights","slug":"xenon-headlights"},{"tax":"pa_sensors","id":104,"name":"Camera","slug":"camera"},{"tax":"pa_sensors","id":102,"name":"Front sensors","slug":"front-sensors"},{"tax":"pa_sensors","id":103,"name":"Rear sensors","slug":"rear-sensors"},{"tax":"pa_sensors","id":105,"name":"Self-steering systems","slug":"self-steering-systems"},{"tax":"pa_system","id":106,"name":"AWD (All Wheel Drive)","slug":"awd-all-wheel-drive"},{"tax":"pa_system","id":108,"name":"FWD (Fear Wheel Drive)","slug":"fwd-fear-wheel-drive"},{"tax":"pa_system","id":107,"name":"RWD (Rear Wheel Drive)","slug":"rwd-rear-wheel-drive"},{"tax":"pa_transmission","id":32,"name":"Automatic","slug":"automatic"},{"tax":"pa_transmission","id":31,"name":"Manual","slug":"manual"},{"tax":"pa_transmission","id":33,"name":"Semi-automatic","slug":"semi-automatic"}]',
						],
					],

					// Revolution Slider
					'revslider'      => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-about.zip',
							'revslider-homepage-slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'        => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":198,"name":"Useful Info"},{"term_id":195,"name":"Car Links"},{"term_id":194,"name":"Services"},{"term_id":197,"name":"Segments"}]}',
						],
					],

					// Typolab
					'typolab'        => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/automotive/',
			],

			// Law
			[
				// Content pack ID
				'id'     => 'law',

				// Name
				'name'   => 'Law',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-law',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-homepage-slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":10,"name":"Our Services"},{"term_id":12,"name":"Our Services 2"},{"term_id":10,"name":"Our Services"},{"term_id":12,"name":"Our Services 2"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/law/',
			],

			// Hotel
			[
				// Content pack ID
				'id'     => 'hotel',

				// Name
				'name'   => 'Hotel',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-hotel',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'Events',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-hotel-slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":3,"name":"Hotel"},{"term_id":5,"name":"Other"},{"term_id":4,"name":"Spa &amp; Wellness"},{"term_id":6,"name":"Restaurant"},{"term_id":3,"name":"Hotel"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/hotel/',
			],

			// Architecture
			[
				// Content pack ID
				'id'     => 'architecture',

				// Name
				'name'   => 'Architecture',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-architecture',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-architecture.zip',
							'revslider-home-two.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/architecture/',
			],

			// Restaurant
			[
				// Content pack ID
				'id'     => 'restaurant',

				// Name
				'name'   => 'Restaurant',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-restaurant',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-homepage.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/restaurant/',
			],

			// Construction
			[
				// Content pack ID
				'id'     => 'construction',

				// Name
				'name'   => 'Construction',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-construction',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-home.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":41,"name":"Services"},{"term_id":42,"name":"Projects"},{"term_id":43,"name":"Company"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/construction/',
			],

			// Travel
			[
				// Content pack ID
				'id'     => 'travel',

				// Name
				'name'   => 'Travel',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-travel',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
							'ninja-forms',
							'bookingcom-official-searchbox',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Home',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Basic',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-main-slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":24,"name":"Basic"},{"term_id":4,"name":"Destinations"},{"term_id":5,"name":"Tickets"},{"term_id":6,"name":"Tourism"},{"term_id":7,"name":"Information"},{"term_id":6,"name":"Tourism"},{"term_id":5,"name":"Tickets"},{"term_id":7,"name":"Information"},{"term_id":4,"name":"Destinations"},{"term_id":24,"name":"Basic"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/travel/',
			],

			// Photography
			[
				// Content pack ID
				'id'     => 'photography',

				// Name
				'name'   => 'Photography',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-photography',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
							'ninja-forms',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Work',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/photography/',
			],

			// Landing Page
			[
				// Content pack ID
				'id'     => 'landing',

				// Name
				'name'   => 'Landing Page',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-landing',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
							],
							'front_page' => 'Homepage',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'One Page Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-landing.zip',
							'revslider-apple-watch.zip',
						],
						'requires' => 'revslider',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/landing/',
			],

			// Shop
			[
				// Content pack ID
				'id'     => 'shop',

				// Name
				'name'   => 'Shop',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-shop',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'woocommerce',
						],
						'options'  => [
							'post_types'  => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'product'    => 'Products',
							],
							'front_page'  => 'Homepage',
							'posts_page'  => 'Blog',
							'menus'       => [
								'main-menu' => 'Main Menu',
							],
							'woocommerce' => [

								// Pages
								'pages'                => [
									'shop'      => 'Shop',
									'cart'      => 'Cart',
									'checkout'  => 'Checkout',
									'myaccount' => 'My Account',
								],

								// Thumbnails
								'thumbnails'           => [
									'cropping'               => 'custom',
									'cropping_custom_width'  => 11,
									'cropping_custom_height' => 14,
									'image_width'            => 550,
								],

								// Other Options
								'options'              => [
									'woocommerce_catalog_columns' => 3,
									'woocommerce_catalog_rows'    => 4,
								],

								// Taxonomy data
								'taxonomy_data'        => '[{"taxonomy":"product_cat","slug":"accessories","name":"Accessories","thumbnail":"2017\/01\/landscape1.jpg"},{"taxonomy":"product_cat","slug":"children","name":"Children","thumbnail":"2017\/01\/landscape4.jpg"},{"taxonomy":"product_cat","slug":"home","name":"Home","thumbnail":"2017\/01\/landscape2.jpg"},{"taxonomy":"product_cat","slug":"magazines","name":"Magazines","thumbnail":"2017\/01\/landscape6.jpg"},{"taxonomy":"product_cat","slug":"office","name":"Office","thumbnail":"2017\/01\/landscape3.jpg"},{"taxonomy":"product_cat","slug":"outdoor","name":"Outdoor","thumbnail":"2017\/01\/landscape5.jpg"},{"taxonomy":"product_tag","slug":"backpack","name":"backpack"},{"taxonomy":"product_tag","slug":"bag","name":"bag"},{"taxonomy":"product_tag","slug":"black","name":"black"},{"taxonomy":"product_tag","slug":"wolfiee","name":"wolfiee"},{"taxonomy":"pa_color","slug":"black","name":"Black"},{"taxonomy":"pa_color","slug":"blue","name":"Blue"},{"taxonomy":"pa_color","slug":"green","name":"Green"},{"taxonomy":"pa_color","slug":"brown","name":"Brown"},{"taxonomy":"pa_color","slug":"red","name":"Red"},{"taxonomy":"pa_color","slug":"turquoise","name":"Turquoise"},{"taxonomy":"pa_color","slug":"white","name":"White"},{"taxonomy":"pa_color","slug":"yellow","name":"Yellow"},{"taxonomy":"pa_dimensions","slug":"h1-5-x-w-2-75-x-d-3-0","name":"H:1.5\" x W: 2.75\" x D: 3.0\""},{"taxonomy":"pa_materials","slug":"aluminium","name":"Aluminium"},{"taxonomy":"pa_materials","slug":"copper","name":"Copper"},{"taxonomy":"pa_materials","slug":"leather","name":"Leather"},{"taxonomy":"pa_materials","slug":"nylon","name":"Nylon"},{"taxonomy":"pa_materials","slug":"steel","name":"Steel"},{"taxonomy":"pa_materials","slug":"wood","name":"Wood"}]',

								// Attribute taxonomies
								'attribute_taxonomies' => '[{"attribute_name":"color","attribute_label":"Color"},{"attribute_name":"dimensions","attribute_label":"Dimensions"},{"attribute_name":"materials","attribute_label":"Materials"}]',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-shop_slider.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":38,"name":"Footer - Home"},{"term_id":39,"name":"Footer - Office"},{"term_id":40,"name":"Footer - Magazines"},{"term_id":41,"name":"Footer - Children"},{"term_id":42,"name":"Footer - Outdoor"},{"term_id":43,"name":"Footer - Wall"}]}'
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/shop/',
			],

			// Education
			[
				// Content pack ID
				'id'     => 'education',

				// Name
				'name'   => 'Education',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-education',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Homepage',
							'posts_page' => 'News',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						]
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-contact.zip',
							'revslider-blog.zip',
							'revslider-homepage-slider.zip',
							'revslider-courses.zip',
							'revslider-news.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type'    => 'wordpress-widgets',
						'src'     => 'widgets.wie',
						'options' => [

							// Widgets data
							'data' => '{"widget_nav_menu":[{"term_id":4,"name":"Footer Menu 2"},{"term_id":37,"name":"Footer Menu 3"},{"term_id":3,"name":"Footer Menu 1"},{"term_id":36,"name":"Footer Menu 4"}]}',
						],
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/education/',
			],

			// Fitness
			[
				// Content pack ID
				'id'     => 'fitness',

				// Name
				'name'   => 'Fitness',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-fitness',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'woocommerce',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types'  => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
								'product'    => 'Products',
							],
							'front_page'  => 'Home',
							'posts_page'  => 'Blog',
							'menus'       => [ 'main-menu' => 'Main Menu' ],
							'woocommerce' => [

								// Pages
								'pages'                => [
									'shop'      => 'Shop',
									'cart'      => 'Cart',
									'checkout'  => 'Checkout',
									'myaccount' => 'My Account',
								],

								// Thumbnails
								'thumbnails'           => [
									'cropping'               => 'custom',
									'cropping_custom_width'  => 4,
									'cropping_custom_height' => 5,
									'image_width'            => 300,
								],

								// Other Options
								'options'              => [
									'woocommerce_catalog_columns' => 3,
									'woocommerce_catalog_rows'    => 4,
								],

								// Taxonomy data
								'taxonomy_data'        => '[{"taxonomy":"product_cat","slug":"concentrated-series","name":"Concentrated Series"},{"taxonomy":"product_cat","slug":"essential-series","name":"Essential Series"},{"taxonomy":"product_cat","slug":"performance-series","name":"Performance Series"},{"taxonomy":"product_cat","slug":"premium-series","name":"Premium Series"},{"taxonomy":"product_cat","slug":"sx-7-series","name":"SX-7 Series"},{"taxonomy":"product_tag","slug":"acid","name":"acid"},{"taxonomy":"product_tag","slug":"amino","name":"amino"},{"taxonomy":"product_tag","slug":"amplifier","name":"amplifier"},{"taxonomy":"product_tag","slug":"anabolic","name":"anabolic"},{"taxonomy":"product_tag","slug":"anotest","name":"anotest"},{"taxonomy":"product_tag","slug":"bcaa","name":"bcaa"},{"taxonomy":"product_tag","slug":"build","name":"build"},{"taxonomy":"product_tag","slug":"cell","name":"cell"},{"taxonomy":"product_tag","slug":"cell-tech","name":"cell tech"},{"taxonomy":"product_tag","slug":"concentrated","name":"concentrated"},{"taxonomy":"product_tag","slug":"creacore","name":"creacore"},{"taxonomy":"product_tag","slug":"creatine","name":"creatine"},{"taxonomy":"product_tag","slug":"double-strenght","name":"double strenght"},{"taxonomy":"product_tag","slug":"elite","name":"elite"},{"taxonomy":"product_tag","slug":"essential","name":"essential"},{"taxonomy":"product_tag","slug":"formula","name":"formula"},{"taxonomy":"product_tag","slug":"fruit-punch","name":"fruit punch"},{"taxonomy":"product_tag","slug":"halo","name":"halo"},{"taxonomy":"product_tag","slug":"hardcore","name":"hardcore"},{"taxonomy":"product_tag","slug":"hydroxycut","name":"hydroxycut"},{"taxonomy":"product_tag","slug":"iso-zero","name":"iso zero"},{"taxonomy":"product_tag","slug":"liquid","name":"liquid"},{"taxonomy":"product_tag","slug":"muscle","name":"muscle"},{"taxonomy":"product_tag","slug":"nano","name":"nano"},{"taxonomy":"product_tag","slug":"performance","name":"performance"},{"taxonomy":"product_tag","slug":"pure","name":"pure"},{"taxonomy":"product_tag","slug":"push10","name":"push10"},{"taxonomy":"product_tag","slug":"shatter","name":"shatter"},{"taxonomy":"product_tag","slug":"stim-free","name":"stim free"},{"taxonomy":"product_tag","slug":"sx7","name":"sx7"},{"taxonomy":"product_tag","slug":"tech","name":"tech"},{"taxonomy":"product_tag","slug":"vapor","name":"vapor"},{"taxonomy":"pa_bcaas","slug":"12g","name":"12g"},{"taxonomy":"pa_bcaas","slug":"15g","name":"15g"},{"taxonomy":"pa_bcaas","slug":"21g","name":"21g"},{"taxonomy":"pa_bcaas","slug":"2g","name":"2g"},{"taxonomy":"pa_bcaas","slug":"4g","name":"4g"},{"taxonomy":"pa_bcaas","slug":"5g","name":"5g"},{"taxonomy":"pa_bcaas","slug":"7g","name":"7g"},{"taxonomy":"pa_bcaas","slug":"8g","name":"8g"},{"taxonomy":"pa_betaine","slug":"11g","name":"11g"},{"taxonomy":"pa_betaine","slug":"12g","name":"12g"},{"taxonomy":"pa_betaine","slug":"1g","name":"1g"},{"taxonomy":"pa_betaine","slug":"2g","name":"2g"},{"taxonomy":"pa_betaine","slug":"3g","name":"3g"},{"taxonomy":"pa_betaine","slug":"4-5g","name":"4.5g"},{"taxonomy":"pa_betaine","slug":"7g","name":"7g"},{"taxonomy":"pa_electrolytes","slug":"no","name":"No"},{"taxonomy":"pa_electrolytes","slug":"yes","name":"Yes"},{"taxonomy":"pa_leucine","slug":"10g","name":"10g"},{"taxonomy":"pa_leucine","slug":"13g","name":"13g"},{"taxonomy":"pa_leucine","slug":"15g","name":"15g"},{"taxonomy":"pa_leucine","slug":"1g","name":"1g"},{"taxonomy":"pa_leucine","slug":"2-5g","name":"2.5g"},{"taxonomy":"pa_leucine","slug":"2g","name":"2g"},{"taxonomy":"pa_leucine","slug":"4g","name":"4g"},{"taxonomy":"pa_leucine","slug":"5-6g","name":"5.6g"},{"taxonomy":"pa_leucine","slug":"5g","name":"5g"},{"taxonomy":"pa_taurine","slug":"0-5g","name":"0.5g"},{"taxonomy":"pa_taurine","slug":"10g","name":"10g"},{"taxonomy":"pa_taurine","slug":"11g","name":"11g"},{"taxonomy":"pa_taurine","slug":"12g","name":"12g"},{"taxonomy":"pa_taurine","slug":"1g","name":"1g"},{"taxonomy":"pa_taurine","slug":"21","name":"21"},{"taxonomy":"pa_taurine","slug":"2g","name":"2g"},{"taxonomy":"pa_taurine","slug":"3g","name":"3g"},{"taxonomy":"pa_taurine","slug":"4g","name":"4g"},{"taxonomy":"pa_taurine","slug":"9g","name":"9g"}]',

								// Attribute taxonomies
								'attribute_taxonomies' => '[{"attribute_name":"bcaas","attribute_label":"BCAAs"},{"attribute_name":"betaine","attribute_label":"Betaine"},{"attribute_name":"electrolytes","attribute_label":"Electrolytes"},{"attribute_name":"leucine","attribute_label":"Leucine"},{"attribute_name":"taurine","attribute_label":"Taurine"}]',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Revolution Slider
					'revslider'     => [
						'type'     => 'revolution-slider',
						'src'      => [
							'revslider-membership.zip',
							'revslider-homepage.zip',
						],
						'requires' => 'revslider',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/fitness/',
			],

			// Freelancer
			[
				// Content pack ID
				'id'     => 'freelancer',

				// Name
				'name'   => 'Freelancer',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-freelancer',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
							'portfolio-post-type',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
								'portfolio'  => 'Portfolio',
							],
							'front_page' => 'Portfolio',
							'posts_page' => 'Blog',
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/freelancer/',
			],

			// Blogging
			[
				// Content pack ID
				'id'     => 'blogging',

				// Name
				'name'   => 'Blogging',

				// Import data
				'import' => [

					// Child theme
					'child-theme'   => [
						'type'    => 'install-child-theme',
						'src'     => 'child-theme.zip',
						'options' => [
							'name' => 'kalium-child-blogging',
						],
					],

					// WordPress Content XML
					'content'       => [
						'type'     => 'wordpress-import',
						'src'      => 'content.xml',
						'requires' => [
							'advanced-custom-fields-pro',
							'js_composer',
						],
						'options'  => [
							'post_types' => [
								'attachment' => 'Media Files',
								'post'       => 'Posts',
								'page'       => 'Page',
							],
							'menus'      => [
								'main-menu' => 'Main Menu',
							],
						],
					],

					// Theme Options
					'theme-options' => [
						'type' => 'theme-options',
						'src'  => 'theme-options-base64.txt',
					],

					// Widgets
					'widgets'       => [
						'type' => 'wordpress-widgets',
						'src'  => 'widgets.wie',
					],

					// Typolab
					'typolab'       => [
						'type' => 'typography-typolab',
						'src'  => 'typolab.json',
					],
				],

				// Preview URL
				'url'    => 'https://demo.kaliumtheme.com/blogging/',
			],
		] );
	}

	/**
	 * Get content pack by ID.
	 *
	 * @param string $content_pack_id
	 *
	 * @return Kalium_Demo_Content_Pack|null
	 */
	public function get_content_pack( $content_pack_id ) {

		// Loop through content packs
		foreach ( $this->get_content_packs() as $content_pack ) {
			if ( $content_pack_id === $content_pack->get_id() ) {
				return $content_pack;
			}
		}

		return null;
	}

	/**
	 * Get installed content packs.
	 *
	 * @return Kalium_Demo_Content_Pack[]
	 */
	public function get_installed_content_packs() {
		$content_packs = [];

		foreach ( $this->get_content_packs() as $content_pack ) {
			if ( $content_pack->is_installed() ) {
				$content_packs[] = $content_pack;
			}
		}

		return $content_packs;
	}

	/**
	 * List demo content packs.
	 *
	 * @return void
	 */
	public function list_demo_content_packs() {

		// Load template file
		kalium()->require_file( __DIR__ . '/includes/views/demos.php', [
			'is_theme_registered'     => kalium()->theme_license->is_theme_registered(),
			'installed_content_packs' => $this->get_installed_content_packs(),
			'content_packs'           => $this->get_content_packs(),
		] );
	}

	/**
	 * Import content pack page as displayed on AJAX.
	 *
	 * @return void
	 */
	public function import_content_pack_page() {

		// Only for allowed users
		if ( current_user_can( 'manage_options' ) ) {

			// Get content pack ID to import
			$content_pack_id = kalium()->request->query( 'content-pack' );

			// Content pack
			if ( $content_pack = $this->get_content_pack( $content_pack_id ) ) {

				// Uninstall content pack
				if ( $content_pack->is_installed() ) {

					// Import instance
					$import_instance       = $content_pack->get_import_instance();
					$imported_content_type = $import_instance->get_imported_content_type();

					// Content uninstall view
					kalium()->require_file( __DIR__ . '/includes/views/content-pack-uninstall.php', [
						'content_pack'          => $content_pack,
						'imported_content_type' => $imported_content_type,
					] );

				} else {

					// Content pack view
					kalium()->require_file( __DIR__ . '/includes/views/content-pack.php', [
						'content_pack' => $content_pack,
					] );
				}
				die();
			}
		}
	}

	/**
	 * Debug import of specific content pack and import ID.
	 *
	 * For testing purpose only.
	 *
	 * @param string $content_pack_id
	 * @param string $import_id
	 * @param array  $args
	 */
	public function debug_content_pack_import( $content_pack_id, $import_id, $args = [] ) {
		$args = wp_parse_args( $args, [
			'do_download'    => false,
			'do_backup'      => false,
			'do_import'      => false,
			'do_complete'    => false,
			'do_remove'      => false,
			'import_checked' => true,
			'args_values'    => [
				'post_types' => [
					'attachment',
					'post',
					'page',
				],
			],
		] );

		// Only when DEBUG is enabled
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			// Content pack
			$content_pack = $this->get_content_pack( $content_pack_id );

			if ( is_null( $content_pack ) ) {
				echo sprintf( 'Demo content pack "%s" doesn\'t exists', $content_pack_id );
				exit();
			}

			// Import content type
			$import = $content_pack->get_import_by_id( $import_id );

			if ( is_null( $import ) ) {
				echo sprintf( 'Import content type "%s" doesn\'t exists', $import_id );
				exit();
			}

			// Import manager
			$import_manager = Kalium_Demo_Import_Manager::get_instance( $content_pack );

			// Backup manager
			$backup_manager = Kalium_Demo_Backup_Manager::get_instance( $content_pack );

			// Assign import manager and backup manager to content pack
			$content_pack->import_manager( $import_manager );
			$content_pack->backup_manager( $backup_manager );

			// Create/resume import instance
			$import_instance = $content_pack->get_import_instance();

			// Set current import id in import instance
			$import_instance->set_import_id( $import->get_import_id() );

			// Set checked status of import field
			$import->is_checked( $args['import_checked'] );

			// Set args values
			$import->set_args_values( $args['args_values'] );

			// Initialize Filesystem
			kalium()->filesystem->initialize();

			// Import task: Download
			if ( $args['do_download'] ) {

				// Clear errors for the current import type
				$import_instance->clear_errors();

				$import->do_download();
			}

			// Import task: Backup
			if ( $args['do_backup'] ) {
				$import->do_backup();
			}

			// Import task: Import
			if ( $args['do_import'] ) {
				$import->do_import();
			}

			// Import task: Complete
			if ( $args['do_complete'] ) {
				$import->do_complete();
			}

			// Import task: Remove
			if ( $args['do_remove'] ) {
				$import->do_remove();
			}

			// Display errors
			if ( $import->get_errors()->has_errors() ) {
				echo sprintf( '<h4>Errors</h4><pre style="padding: 20px; background: #eee;">%s</pre>', $import->get_errors()->get_error_message() );
			}

			// Import instance
			echo sprintf( '<h4>Import Instance <small>(%s)</small></h4><pre style="padding: 20px; background: #eee;">%s</pre>', $import->get_name(), print_r( $import_instance->to_array(), true ) );
		}
	}
}

// Instantiate Demo Content Import class
Kalium_Demo_Content_Importer::instance();
