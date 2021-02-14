<?php
/*
 * Plugin Name: Flexible SSL for CloudFlare
 * Plugin URI: https://icwp.io/cloudflaresslpluginauthor
 * Description: Fix For CloudFlare Flexible SSL Redirect Loop For WordPress
 * Version: 1.3.1
 * Text Domain: cloudflare-flexible-ssl
 * Author: One Dollar Plugin
 * Author URI: https://icwp.io/cloudflaresslpluginauthor
 */

/**
 * Copyright (c) 2020 One Dollar Plugin <support@shieldsecurity.io>
 * All rights reserved.
 * "CloudFlare Flexible SSL" plugin is distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
class ICWP_Cloudflare_Flexible_SSL {

	public function __construct() {
	}

	public function run() {
		if ( !$this->isSsl() && $this->isSslToNonSslProxy() ) {
			$_SERVER[ 'HTTPS' ] = 'on';
			add_action( 'shutdown', array( $this, 'maintainPluginLoadPosition' ) );
		}
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			include_once( dirname( __FILE__ ).'/shieldprom.php' );
		}
	}

	/**
	 * @return bool
	 */
	private function isSsl() {
		return function_exists( 'is_ssl' ) && is_ssl();
	}

	/**
	 * @return bool
	 */
	private function isSslToNonSslProxy() {
		$bIsProxy = false;

		$aServerKeys = array( 'HTTP_CF_VISITOR', 'HTTP_X_FORWARDED_PROTO' );
		foreach ( $aServerKeys as $sKey ) {
			if ( isset( $_SERVER[ $sKey ] ) && ( strpos( $_SERVER[ $sKey ], 'https' ) !== false ) ) {
				$bIsProxy = true;
				break;
			}
		}

		return $bIsProxy;
	}

	/**
	 * Sets this plugin to be the first loaded of all the plugins.
	 */
	public function maintainPluginLoadPosition() {
		$sBaseFile = plugin_basename( __FILE__ );
		$nLoadPosition = $this->getActivePluginLoadPosition( $sBaseFile );
		if ( $nLoadPosition > 1 ) {
			$this->setActivePluginLoadPosition( $sBaseFile, 0 );
		}
	}

	/**
	 * @param string $sPluginFile
	 * @return int
	 */
	private function getActivePluginLoadPosition( $sPluginFile ) {
		$sOptionKey = is_multisite() ? 'active_sitewide_plugins' : 'active_plugins';
		$aActive = get_option( $sOptionKey );
		$nPosition = -1;
		if ( is_array( $aActive ) ) {
			$nPosition = array_search( $sPluginFile, $aActive );
			if ( $nPosition === false ) {
				$nPosition = -1;
			}
		}
		return $nPosition;
	}

	/**
	 * @param string $sPluginFile
	 * @param int    $nDesiredPosition
	 */
	private function setActivePluginLoadPosition( $sPluginFile, $nDesiredPosition = 0 ) {

		$aActive = $this->setArrayValueToPosition( get_option( 'active_plugins' ), $sPluginFile, $nDesiredPosition );
		update_option( 'active_plugins', $aActive );

		if ( is_multisite() ) {
			$aActive = $this->setArrayValueToPosition( get_option( 'active_sitewide_plugins' ), $sPluginFile, $nDesiredPosition );
			update_option( 'active_sitewide_plugins', $aActive );
		}
	}

	/**
	 * @param array $aSubjectArray
	 * @param mixed $mValue
	 * @param int   $nDesiredPosition
	 * @return array
	 */
	private function setArrayValueToPosition( $aSubjectArray, $mValue, $nDesiredPosition ) {

		if ( $nDesiredPosition < 0 || !is_array( $aSubjectArray ) ) {
			return $aSubjectArray;
		}

		$nMaxPossiblePosition = count( $aSubjectArray ) - 1;
		if ( $nDesiredPosition > $nMaxPossiblePosition ) {
			$nDesiredPosition = $nMaxPossiblePosition;
		}

		$nPosition = array_search( $mValue, $aSubjectArray );
		if ( $nPosition !== false && $nPosition != $nDesiredPosition ) {

			// remove existing and reset index
			unset( $aSubjectArray[ $nPosition ] );
			$aSubjectArray = array_values( $aSubjectArray );

			// insert and update
			// http://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
			array_splice( $aSubjectArray, $nDesiredPosition, 0, $mValue );
		}

		return $aSubjectArray;
	}
}

$oIcwpCfFlexibleSslCheck = new ICWP_Cloudflare_Flexible_SSL();
$oIcwpCfFlexibleSslCheck->run();