<?php
/**
 * Kalium WordPress Theme
 *
 * GDPR notices class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Admin_GDPR_Notices {

	/**
	 * Admin Notices holder.
     *
     * @var array
	 */
	public $admin_notices = [];

	/**
	 * Constructor.
     *
     * @return void
	 */
	public function __construct() {
		global $pagenow;

		// Execute only on admin side
		// Any logged user that has access to these pages can see the Data Protection Notice
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return;
		}

		// Dismissed
		$dismissed = (int) get_user_meta( get_current_user_id(), 'dismissed_kalium_gdpr_notice_date', true );

		if ( $dismissed && ( time() - $dismissed ) < MONTH_IN_SECONDS * 6 ) {
			return;
		}

		// Current page
		$page = kalium()->request->query( 'page' );

		if ( 'update-core.php' === $pagenow ) {
			$page = 'update-core';
		}

		if ( ! empty( $page ) ) {
			$message = $this->get_privacy_message_for_page( $page );

			if ( $message ) {
				$this->admin_notices[] = $message;
				add_action( 'admin_notices', [ $this, 'display_admin_notices' ] );
			}
		}

		// Dismiss AJAX Action
		add_action( 'wp_ajax_kalium_data_protection_notice_dismiss', [ $this, 'dismiss_notice' ] );
	}

	/**
	 * Message notices for pages where data is sent or is being sent.
	 *
	 * @param string $page
	 *
	 * @return string
	 */
	public function get_privacy_message_for_page( $page ) {
		$message            = '';
		$data_license_key   = kalium()->theme_license->is_theme_registered() ? kalium()->theme_license->get_license_key() : sprintf( 'Theme not registered (<a href="%s">register theme</a>)', Kalium_About::get_tab_link( 'theme-registration' ) );
		$data_theme_version = kalium()->get_version();

		// Registered messages
		$messages = [
			'update-core'                 => sprintf( 'Following data is sent to Laborator.co servers located in France to retrieve latest version of the theme: %s', $this->data_entries_table( [
				'License Key'   => $data_license_key,
				'Theme Version' => $data_theme_version,
			] ) ),
			'theme-registration'          => sprintf( 'By clicking <strong>Register Theme</strong> button the following data will be sent to Laborator.co servers located in France: %s', $this->data_entries_table( [
				'Site URL'      => home_url(),
				'Referring URL' => admin_url(),
				'Theme Version' => $data_theme_version,
			] ) ),
			'theme-registration-validate' => sprintf( 'Following data is sent to Laborator.co servers located in France: %s', $this->data_entries_table( [
				'License Key' => $data_license_key,
			] ) ),
			'plugins'                     => sprintf( 'Following data is sent to Laborator.co servers located in France to retrieve latest plugin versions: %s', $this->data_entries_table( [
				'License Key'   => $data_license_key,
				'Theme Version' => $data_theme_version,
			] ) ),
		];

		switch ( $page ) {

			// Core Update Page
			case 'update-core':
				$message = $messages['update-core'];
				break;

			// Product Registration page
			case 'kalium':
				if ( 'theme-registration' === kalium()->request->query( 'tab' ) ) {
					if ( false === kalium()->theme_license->is_theme_registered() ) {
						$message = $messages['theme-registration'];
					}
				} else if ( 'plugins' === kalium()->request->query( 'tab' ) ) {
					$message = $messages['plugins'];
				} else if ( 'validate-theme-registration' === kalium()->request->query( 'action' ) ) {
					$message = $messages['theme-registration-validate'];
				}
				break;

			// Bundled Plugins page
			case 'kalium-install-plugins':
				if ( ! kalium()->request->has( 'plugin' ) ) {
					$message = $messages['plugins'];
				}
				break;
		}

		return $message;
	}

	/**
	 * Data entries.
	 *
	 * @param array $entries
	 *
	 * @return string
	 */
	public function data_entries_table( $entries = [] ) {
		ob_start();
		?>
        <table class="table data-entries-table" cellpadding="0" cellspacing="0">
            <tbody>
			<?php
			foreach ( $entries as $type => $value ) {
				?>
                <tr>
                    <th><?php echo esc_html( $type ); ?>:</th>
                    <td><?php echo wp_kses_post( $value ); ?></td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Display admin notices regarding GDPR.
     *
     * @return void
	 */
	public function display_admin_notices() {
		$this->notice_wrapper_start();

		foreach ( $this->admin_notices as $admin_notice ) {
			echo wpautop( wp_kses_post( $admin_notice ) );
		}

		$this->notice_wrapper_end();
	}

	/**
	 * Notice wrapper start.
     *
     * @return void
	 */
	public function notice_wrapper_start() {
		?>
        <div class="laborator-notice notice notice-info is-dismissible" id="kalium-gdpr-notice-container">
        <h4>Data Protection Notice</h4>
		<?php
	}

	/**
	 * Notice wrapper end.
	 *
	 * @return void
	 */
	public function notice_wrapper_end() {
		?>
        <p><a href="https://laborator.co" rel="noopener" target="_blank">Laborator</a> will never collect any
            information of your site unless stated.</p>
        </div>
		<?php
	}

	/**
	 * Dismiss notice action.
	 *
	 * @return void
	 */
	public function dismiss_notice() {
	    if ( ! is_user_logged_in() ) {
	        return;
		}

		update_user_meta( get_current_user_id(), 'dismissed_kalium_gdpr_notice_date', time() );
		die( '1' );
	}
}
