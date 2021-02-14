<?php

add_action( 'init', function () {

	if ( !class_exists( 'ICWP_WPSF_Shield_Security' ) && current_user_can( 'manage_options' ) ) {
		$nCurrentId = wp_get_current_user()->ID;

		$sMetaKey = 'odp_cfs_shield_notice';

		$nCurrentTime = get_user_meta( $nCurrentId, $sMetaKey, true );
		if ( empty( $nCurrentTime ) ) {
			update_user_meta( $nCurrentId, $sMetaKey, 'Y' );
		}
		elseif ( isset( $_GET[ 'flag' ] ) && $_GET[ 'flag' ] == $sMetaKey ) {
			// set flag that the dialogue has been acknowledged closed
			update_user_meta( $nCurrentId, $sMetaKey, time() );
		}
		elseif ( $nCurrentTime === 'Y' ) { // we can successfully set meta but haven't closed dialogue
			add_action( 'admin_notices', 'odp_shield_promo_notice' );
			add_action( 'network_admin_notices', 'odp_shield_promo_notice' );
		}
	}
} );

function odp_shield_promo_notice() {
	$aText = [
		"Take a quick moment to checkout Shield Security - downloaded over 5Million times with a avg 5* satisfied rating.",
		'Built by the <span style="text-decoration: underline">same people you trusted</span> to help you easily setup CloudFlare Flexible SSL.'
	];

	global $pagenow;

	echo sprintf(
		'<div class="updated"><h4>%s</h4><p>%s</p>'.
		'<p><a href="%s" target="_blank" style="font-weight: bolder">%s</a>'.
		' / <a href="%s">%s</a></p></div>',

		ucwords( 'Looking for a fresh, powerful security plugin for WordPress?' ),
		implode( '<br/>', $aText ),
		add_query_arg(
			[
				's'    => 'Shield+Security+for+WordPress+by+One+Dollar+Plugin',
				'tab'  => 'search',
				'type' => 'term',
				'flag' => 'odp_cfs_shield_notice'
			],
			network_admin_url( 'plugin-install.php' )
		),
		' &rarr; Click here to discover Shield Security for WordPress',
		add_query_arg( [ 'flag' => 'odp_cfs_shield_notice' ], $pagenow ),
		'Close This Notice'
	);
}