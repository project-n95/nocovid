<?php
/**
 *    Custom Font Icons
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

function lab_vc_custom_icon_fonts() {

	// Add Extra Icon Fonts
	$param = WPBMap::getParam( 'vc_icon', 'type' );

	if ( ! is_array( $param ) ) {
		return false;
	}

	$param['weight'] = 2;

	$param['value'] = [ 'Linea' => 'linea' ] + $param['value'];

	vc_update_shortcode_param( 'vc_icon', $param );

	// Add Param Type
	$attributes = [
		'type'        => 'iconpicker',
		'heading'     => __( 'Icon', 'lab_composer' ),
		'param_name'  => 'icon_linea',
		'value'       => 'icon-basic-accelerator',
		'weight'      => 1,
		'settings'    => [
			'emptyIcon'    => false,
			'type'         => 'linea',
			'iconsPerPage' => - 1,
		],
		'dependency'  => [
			'element' => 'type',
			'value'   => 'linea',
		],
		'description' => 'Select icon from library.',
	];

	vc_add_param( 'vc_icon', $attributes );


	// Set Default Color to Black
	$param        = WPBMap::getParam( 'vc_icon', 'color' );
	$param['std'] = 'black';

	vc_update_shortcode_param( 'vc_icon', $param );


	// Custom Icon
	add_action( 'admin_print_styles', 'lab_vc_custom_icon_css' );
}

function lab_vc_custom_icon_css() {
	// Change Icon for VC_ICON element
	$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/icon.png' );

	?>
    <style>
        .wpb-elements-list-modal li[data-element="vc_icon"] .vc_element-icon {
            background-image: url(<?php echo $lab_vc_element_icon; ?>) !important;
            background-position: center center;
            background-size: 40px;
        }
    </style>
	<?php
}

function lab_vc_custom_icon_fonts_enqueue( $font ) {
	switch ( $font ) {
		case "linea":
			kalium_enqueue( 'font-lineaicons-css' );
			break;
	}
}

function lab_vc_iconpicker_editor_jscss() {
	kalium_enqueue( 'font-lineaicons-css' );
}

function lab_custom_icon_font_list_linea( $icons ) {
	$linea_icons = [
		[ 'icon-basic-accelerator' => 'Accelerator' ],
		[ 'icon-basic-alarm' => 'Alarm' ],
		[ 'icon-basic-anchor' => 'Anchor' ],
		[ 'icon-basic-anticlockwise' => 'Anticlockwise' ],
		[ 'icon-basic-archive' => 'Archive' ],
		[ 'icon-basic-archive-full' => 'Archive Full' ],
		[ 'icon-basic-ban' => 'Ban' ],
		[ 'icon-basic-battery-charge' => 'Battery Charge' ],
		[ 'icon-basic-battery-empty' => 'Battery Empty' ],
		[ 'icon-basic-battery-full' => 'Battery Full' ],
		[ 'icon-basic-battery-half' => 'Battery Half' ],
		[ 'icon-basic-bolt' => 'Bolt' ],
		[ 'icon-basic-book' => 'Book' ],
		[ 'icon-basic-book-pen' => 'Book Pen' ],
		[ 'icon-basic-book-pencil' => 'Book Pencil' ],
		[ 'icon-basic-bookmark' => 'Bookmark' ],
		[ 'icon-basic-calculator' => 'Calculator' ],
		[ 'icon-basic-calendar' => 'Calendar' ],
		[ 'icon-basic-cards-diamonds' => 'Cards Diamonds' ],
		[ 'icon-basic-cards-hearts' => 'Cards Hearts' ],
		[ 'icon-basic-case' => 'Case' ],
		[ 'icon-basic-chronometer' => 'Chronometer' ],
		[ 'icon-basic-clessidre' => 'Clessidre' ],
		[ 'icon-basic-clock' => 'Clock' ],
		[ 'icon-basic-clockwise' => 'Clockwise' ],
		[ 'icon-basic-cloud' => 'Cloud' ],
		[ 'icon-basic-clubs' => 'Clubs' ],
		[ 'icon-basic-compass' => 'Compass' ],
		[ 'icon-basic-cup' => 'Cup' ],
		[ 'icon-basic-diamonds' => 'Diamonds' ],
		[ 'icon-basic-display' => 'Display' ],
		[ 'icon-basic-download' => 'Download' ],
		[ 'icon-basic-exclamation' => 'Exclamation' ],
		[ 'icon-basic-eye' => 'Eye' ],
		[ 'icon-basic-eye-closed' => 'Eye Closed' ],
		[ 'icon-basic-female' => 'Female' ],
		[ 'icon-basic-flag1' => 'Flag1' ],
		[ 'icon-basic-flag2' => 'Flag2' ],
		[ 'icon-basic-floppydisk' => 'Floppydisk' ],
		[ 'icon-basic-folder' => 'Folder' ],
		[ 'icon-basic-folder-multiple' => 'Folder Multiple' ],
		[ 'icon-basic-gear' => 'Gear' ],
		[ 'icon-basic-geolocalize-01' => 'Geolocalize 01' ],
		[ 'icon-basic-geolocalize-05' => 'Geolocalize 05' ],
		[ 'icon-basic-globe' => 'Globe' ],
		[ 'icon-basic-gunsight' => 'Gunsight' ],
		[ 'icon-basic-hammer' => 'Hammer' ],
		[ 'icon-basic-headset' => 'Headset' ],
		[ 'icon-basic-heart' => 'Heart' ],
		[ 'icon-basic-heart-broken' => 'Heart Broken' ],
		[ 'icon-basic-helm' => 'Helm' ],
		[ 'icon-basic-home' => 'Home' ],
		[ 'icon-basic-info' => 'Info' ],
		[ 'icon-basic-ipod' => 'Ipod' ],
		[ 'icon-basic-joypad' => 'Joypad' ],
		[ 'icon-basic-key' => 'Key' ],
		[ 'icon-basic-keyboard' => 'Keyboard' ],
		[ 'icon-basic-laptop' => 'Laptop' ],
		[ 'icon-basic-life-buoy' => 'Life Buoy' ],
		[ 'icon-basic-lightbulb' => 'Lightbulb' ],
		[ 'icon-basic-link' => 'Link' ],
		[ 'icon-basic-lock' => 'Lock' ],
		[ 'icon-basic-lock-open' => 'Lock Open' ],
		[ 'icon-basic-magic-mouse' => 'Magic Mouse' ],
		[ 'icon-basic-magnifier' => 'Magnifier' ],
		[ 'icon-basic-magnifier-minus' => 'Magnifier Minus' ],
		[ 'icon-basic-magnifier-plus' => 'Magnifier Plus' ],
		[ 'icon-basic-mail' => 'Mail' ],
		[ 'icon-basic-mail-multiple' => 'Mail Multiple' ],
		[ 'icon-basic-mail-open' => 'Mail Open' ],
		[ 'icon-basic-mail-open-text' => 'Mail Open Text' ],
		[ 'icon-basic-male' => 'Male' ],
		[ 'icon-basic-map' => 'Map' ],
		[ 'icon-basic-message' => 'Message' ],
		[ 'icon-basic-message-multiple' => 'Message Multiple' ],
		[ 'icon-basic-message-txt' => 'Message Txt' ],
		[ 'icon-basic-mixer2' => 'Mixer2' ],
		[ 'icon-basic-mouse' => 'Mouse' ],
		[ 'icon-basic-notebook' => 'Notebook' ],
		[ 'icon-basic-notebook-pen' => 'Notebook Pen' ],
		[ 'icon-basic-notebook-pencil' => 'Notebook Pencil' ],
		[ 'icon-basic-paperplane' => 'Paperplane' ],
		[ 'icon-basic-pencil-ruler' => 'Pencil Ruler' ],
		[ 'icon-basic-pencil-ruler-pen' => 'Pencil Ruler Pen' ],
		[ 'icon-basic-photo' => 'Photo' ],
		[ 'icon-basic-picture' => 'Picture' ],
		[ 'icon-basic-picture-multiple' => 'Picture Multiple' ],
		[ 'icon-basic-pin1' => 'Pin1' ],
		[ 'icon-basic-pin2' => 'Pin2' ],
		[ 'icon-basic-postcard' => 'Postcard' ],
		[ 'icon-basic-postcard-multiple' => 'Postcard Multiple' ],
		[ 'icon-basic-printer' => 'Printer' ],
		[ 'icon-basic-question' => 'Question' ],
		[ 'icon-basic-rss' => 'Rss' ],
		[ 'icon-basic-server' => 'Server' ],
		[ 'icon-basic-server2' => 'Server2' ],
		[ 'icon-basic-server-cloud' => 'Server Cloud' ],
		[ 'icon-basic-server-download' => 'Server Download' ],
		[ 'icon-basic-server-upload' => 'Server Upload' ],
		[ 'icon-basic-settings' => 'Settings' ],
		[ 'icon-basic-share' => 'Share' ],
		[ 'icon-basic-sheet' => 'Sheet' ],
		[ 'icon-basic-sheet-multiple' => 'Sheet Multiple' ],
		[ 'icon-basic-sheet-pen' => 'Sheet Pen' ],
		[ 'icon-basic-sheet-pencil' => 'Sheet Pencil' ],
		[ 'icon-basic-sheet-txt' => 'Sheet Txt' ],
		[ 'icon-basic-signs' => 'Signs' ],
		[ 'icon-basic-smartphone' => 'Smartphone' ],
		[ 'icon-basic-spades' => 'Spades' ],
		[ 'icon-basic-spread' => 'Spread' ],
		[ 'icon-basic-spread-bookmark' => 'Spread Bookmark' ],
		[ 'icon-basic-spread-text' => 'Spread Text' ],
		[ 'icon-basic-spread-text-bookmark' => 'Spread Text Bookmark' ],
		[ 'icon-basic-star' => 'Star' ],
		[ 'icon-basic-tablet' => 'Tablet' ],
		[ 'icon-basic-target' => 'Target' ],
		[ 'icon-basic-todo' => 'Todo' ],
		[ 'icon-basic-todo-pen' => 'Todo Pen' ],
		[ 'icon-basic-todo-pencil' => 'Todo Pencil' ],
		[ 'icon-basic-todo-txt' => 'Todo Txt' ],
		[ 'icon-basic-todolist-pen' => 'Todolist Pen' ],
		[ 'icon-basic-todolist-pencil' => 'Todolist Pencil' ],
		[ 'icon-basic-trashcan' => 'Trashcan' ],
		[ 'icon-basic-trashcan-full' => 'Trashcan Full' ],
		[ 'icon-basic-trashcan-refresh' => 'Trashcan Refresh' ],
		[ 'icon-basic-trashcan-remove' => 'Trashcan Remove' ],
		[ 'icon-basic-upload' => 'Upload' ],
		[ 'icon-basic-usb' => 'Usb' ],
		[ 'icon-basic-video' => 'Video' ],
		[ 'icon-basic-watch' => 'Watch' ],
		[ 'icon-basic-webpage' => 'Webpage' ],
		[ 'icon-basic-webpage-img-txt' => 'Webpage Img Txt' ],
		[ 'icon-basic-webpage-multiple' => 'Webpage Multiple' ],
		[ 'icon-basic-webpage-txt' => 'Webpage Txt' ],
		[ 'icon-basic-world' => 'World' ],
		[ 'icon-ecommerce-bag' => 'Bag' ],
		[ 'icon-ecommerce-bag-check' => 'Bag Check' ],
		[ 'icon-ecommerce-bag-cloud' => 'Bag Cloud' ],
		[ 'icon-ecommerce-bag-download' => 'Bag Download' ],
		[ 'icon-ecommerce-bag-minus' => 'Bag Minus' ],
		[ 'icon-ecommerce-bag-plus' => 'Bag Plus' ],
		[ 'icon-ecommerce-bag-refresh' => 'Bag Refresh' ],
		[ 'icon-ecommerce-bag-remove' => 'Bag Remove' ],
		[ 'icon-ecommerce-bag-search' => 'Bag Search' ],
		[ 'icon-ecommerce-bag-upload' => 'Bag Upload' ],
		[ 'icon-ecommerce-banknote' => 'Banknote' ],
		[ 'icon-ecommerce-banknotes' => 'Banknotes' ],
		[ 'icon-ecommerce-basket' => 'Basket' ],
		[ 'icon-ecommerce-basket-check' => 'Basket Check' ],
		[ 'icon-ecommerce-basket-cloud' => 'Basket Cloud' ],
		[ 'icon-ecommerce-basket-download' => 'Basket Download' ],
		[ 'icon-ecommerce-basket-minus' => 'Basket Minus' ],
		[ 'icon-ecommerce-basket-plus' => 'Basket Plus' ],
		[ 'icon-ecommerce-basket-refresh' => 'Basket Refresh' ],
		[ 'icon-ecommerce-basket-remove' => 'Basket Remove' ],
		[ 'icon-ecommerce-basket-search' => 'Basket Search' ],
		[ 'icon-ecommerce-basket-upload' => 'Basket Upload' ],
		[ 'icon-ecommerce-bath' => 'Bath' ],
		[ 'icon-ecommerce-cart' => 'Cart' ],
		[ 'icon-ecommerce-cart-check' => 'Cart Check' ],
		[ 'icon-ecommerce-cart-cloud' => 'Cart Cloud' ],
		[ 'icon-ecommerce-cart-content' => 'Cart Content' ],
		[ 'icon-ecommerce-cart-download' => 'Cart Download' ],
		[ 'icon-ecommerce-cart-minus' => 'Cart Minus' ],
		[ 'icon-ecommerce-cart-plus' => 'Cart Plus' ],
		[ 'icon-ecommerce-cart-refresh' => 'Cart Refresh' ],
		[ 'icon-ecommerce-cart-remove' => 'Cart Remove' ],
		[ 'icon-ecommerce-cart-search' => 'Cart Search' ],
		[ 'icon-ecommerce-cart-upload' => 'Cart Upload' ],
		[ 'icon-ecommerce-cent' => 'Cent' ],
		[ 'icon-ecommerce-colon' => 'Colon' ],
		[ 'icon-ecommerce-creditcard' => 'Creditcard' ],
		[ 'icon-ecommerce-diamond' => 'Diamond' ],
		[ 'icon-ecommerce-dollar' => 'Dollar' ],
		[ 'icon-ecommerce-euro' => 'Euro' ],
		[ 'icon-ecommerce-franc' => 'Franc' ],
		[ 'icon-ecommerce-gift' => 'Gift' ],
		[ 'icon-ecommerce-graph1' => 'Graph1' ],
		[ 'icon-ecommerce-graph2' => 'Graph2' ],
		[ 'icon-ecommerce-graph3' => 'Graph3' ],
		[ 'icon-ecommerce-graph-decrease' => 'Graph Decrease' ],
		[ 'icon-ecommerce-graph-increase' => 'Graph Increase' ],
		[ 'icon-ecommerce-guarani' => 'Guarani' ],
		[ 'icon-ecommerce-kips' => 'Kips' ],
		[ 'icon-ecommerce-lira' => 'Lira' ],
		[ 'icon-ecommerce-megaphone' => 'Megaphone' ],
		[ 'icon-ecommerce-money' => 'Money' ],
		[ 'icon-ecommerce-naira' => 'Naira' ],
		[ 'icon-ecommerce-pesos' => 'Pesos' ],
		[ 'icon-ecommerce-pound' => 'Pound' ],
		[ 'icon-ecommerce-receipt' => 'Receipt' ],
		[ 'icon-ecommerce-receipt-bath' => 'Receipt Bath' ],
		[ 'icon-ecommerce-receipt-cent' => 'Receipt Cent' ],
		[ 'icon-ecommerce-receipt-dollar' => 'Receipt Dollar' ],
		[ 'icon-ecommerce-receipt-euro' => 'Receipt Euro' ],
		[ 'icon-ecommerce-receipt-franc' => 'Receipt Franc' ],
		[ 'icon-ecommerce-receipt-guarani' => 'Receipt Guarani' ],
		[ 'icon-ecommerce-receipt-kips' => 'Receipt Kips' ],
		[ 'icon-ecommerce-receipt-lira' => 'Receipt Lira' ],
		[ 'icon-ecommerce-receipt-naira' => 'Receipt Naira' ],
		[ 'icon-ecommerce-receipt-pesos' => 'Receipt Pesos' ],
		[ 'icon-ecommerce-receipt-pound' => 'Receipt Pound' ],
		[ 'icon-ecommerce-receipt-rublo' => 'Receipt Rublo' ],
		[ 'icon-ecommerce-receipt-rupee' => 'Receipt Rupee' ],
		[ 'icon-ecommerce-receipt-tugrik' => 'Receipt Tugrik' ],
		[ 'icon-ecommerce-receipt-won' => 'Receipt Won' ],
		[ 'icon-ecommerce-receipt-yen' => 'Receipt Yen' ],
		[ 'icon-ecommerce-receipt-yen2' => 'Receipt Yen2' ],
		[ 'icon-ecommerce-recept-colon' => 'Recept Colon' ],
		[ 'icon-ecommerce-rublo' => 'Rublo' ],
		[ 'icon-ecommerce-rupee' => 'Rupee' ],
		[ 'icon-ecommerce-safe' => 'Safe' ],
		[ 'icon-ecommerce-sale' => 'Sale' ],
		[ 'icon-ecommerce-sales' => 'Sales' ],
		[ 'icon-ecommerce-ticket' => 'Ticket' ],
		[ 'icon-ecommerce-tugriks' => 'Tugriks' ],
		[ 'icon-ecommerce-wallet' => 'Wallet' ],
		[ 'icon-ecommerce-won' => 'Won' ],
		[ 'icon-ecommerce-yen' => 'Yen' ],
		[ 'icon-ecommerce-yen2' => 'Yen2' ],
		[ 'icon-music-beginning-button' => 'Beginning Button' ],
		[ 'icon-music-bell' => 'Bell' ],
		[ 'icon-music-cd' => 'Cd' ],
		[ 'icon-music-diapason' => 'Diapason' ],
		[ 'icon-music-eject-button' => 'Eject Button' ],
		[ 'icon-music-end-button' => 'End Button' ],
		[ 'icon-music-fastforward-button' => 'Fastforward Button' ],
		[ 'icon-music-headphones' => 'Headphones' ],
		[ 'icon-music-ipod' => 'Ipod' ],
		[ 'icon-music-loudspeaker' => 'Loudspeaker' ],
		[ 'icon-music-microphone' => 'Microphone' ],
		[ 'icon-music-microphone-old' => 'Microphone Old' ],
		[ 'icon-music-mixer' => 'Mixer' ],
		[ 'icon-music-mute' => 'Mute' ],
		[ 'icon-music-note-multiple' => 'Note Multiple' ],
		[ 'icon-music-note-single' => 'Note Single' ],
		[ 'icon-music-pause-button' => 'Pause Button' ],
		[ 'icon-music-play-button' => 'Play Button' ],
		[ 'icon-music-playlist' => 'Playlist' ],
		[ 'icon-music-radio-ghettoblaster' => 'Radio Ghettoblaster' ],
		[ 'icon-music-radio-portable' => 'Radio Portable' ],
		[ 'icon-music-record' => 'Record' ],
		[ 'icon-music-recordplayer' => 'Recordplayer' ],
		[ 'icon-music-repeat-button' => 'Repeat Button' ],
		[ 'icon-music-rewind-button' => 'Rewind Button' ],
		[ 'icon-music-shuffle-button' => 'Shuffle Button' ],
		[ 'icon-music-stop-button' => 'Stop Button' ],
		[ 'icon-music-tape' => 'Tape' ],
		[ 'icon-music-volume-down' => 'Volume Down' ],
		[ 'icon-music-volume-up' => 'Volume Up' ],
	];

	return array_merge( $icons, $linea_icons );
}


add_action( 'vc_after_init', 'lab_vc_custom_icon_fonts' );
add_action( 'vc_enqueue_font_icon_element', 'lab_vc_custom_icon_fonts_enqueue' );

add_action( 'vc_backend_editor_enqueue_js_css', 'lab_vc_iconpicker_editor_jscss' );
add_action( 'vc_frontend_editor_enqueue_js_css', 'lab_vc_iconpicker_editor_jscss' );

add_filter( 'vc_iconpicker-type-linea', 'lab_custom_icon_font_list_linea' ); // Linea Icons List