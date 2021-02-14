<?php
/**
 * Kalium WordPress Theme
 *
 * File system class of Kalium
 *
 * @link https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Magic methods inherited from WP_Filesystem_Base.
 *
 * @method string abspath()
 * @method string wp_content_dir()
 * @method string|false get_contents( $file )
 * @method bool put_contents( $file, $contents, $mode = false )
 * @method bool copy( $source, $destination, $overwrite = false, $mode = false )
 * @method bool move( $source, $destination, $overwrite = false )
 * @method bool delete( $file, $recursive = false, $type = false )
 * @method bool exists( $file )
 * @method int|false size( $file )
 * @method bool rmdir( $path, $recursive = false )
 * @method array|false dirlist( $path, $include_hidden = true, $recursive = false )
 * @method bool touch( $file, $time = 0, $atime = 0 )
 * @method bool mkdir( $path, $chmod = false, $chown = false, $chgrp = false )
 */
class Kalium_Filesystem {

	/**
	 * Status of filesystem.
	 *
	 * @var bool
	 */
	private $ok = false;

	/**
	 * Credentials form.
	 *
	 * @var string
	 */
	private $credentials_form = '';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Execute filesystem methods.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed|WP_Error
	 */
	public function __call( $name, $arguments ) {

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		// When filesystem is not initialized or credentials are not correct
		if ( ! $this->ok ) {
			return $this->error_not_initialized();
		}

		// Allowed methods
		$fs_methods = [
			'abspath',
			'wp_content_dir',
			'get_contents',
			'put_contents',
			'copy',
			'move',
			'delete',
			'exists',
			'size',
			'rmdir',
			'dirlist',
			'touch',
			'mkdir',
		];

		// Invoke mapped methods only
		if ( in_array( $name, $fs_methods ) ) {

			// Replace source ABSPATH
			if ( isset( $arguments[0] ) && in_array( $name, $fs_methods ) ) {
				$arguments[0] = $this->real_abspath( $arguments[0] );
			}

			// Replace destination ABSPATH
			if ( in_array( $name, [ 'copy', 'move' ] ) && isset( $arguments[1] ) ) {
				$arguments[1] = $this->real_abspath( $arguments[1] );
			}

			return call_user_func_array( [ $wp_filesystem, $name ], $arguments );
		}

		return new WP_Error( 'kalium_fs_method_not_exists', sprintf( 'Method "%s" doesn\'t exists!', $name ) );
	}

	/**
	 * Error when Kalium_Filesystem is not initialized.
	 *
	 * @return WP_Error
	 */
	private function error_not_initialized() {
		return new WP_Error( 'kalium_fs_not_initialized', 'Filesystem is not initialized or FTP credentials are not correct' );
	}

	/**
	 * Initialize filesystem.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function initialize( $url = '' ) {

		// Initialed status
		static $initialized = false;

		// Initialize only once
		if ( false === $initialized ) {

			// Load filesystem functions
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			// Credentials
			$creds = $this->get_credentials( $url, true );

			// Setup system global
			if ( $creds ) {

				if ( WP_Filesystem( $creds ) ) {
					$this->ok = true;
				}
			}

			// Mark as initialized
			$initialized = true;
		}

		return $this->ok;
	}

	/**
	 * Get filesystem credentials.
	 *
	 * @param string $url
	 * @param bool   $silent
	 *
	 * @return bool
	 */
	public function get_credentials( $url = '', $silent = false ) {

		// Direct method, no work required
		if ( 'direct' === get_filesystem_method() ) {
			$creds = request_filesystem_credentials( esc_url_raw( $url ) );
		} // FTP/FTPS or SSH
		else {

			ob_start();

			// Request credentials
			$creds = request_filesystem_credentials( esc_url_raw( $url ) );

			// Save FTP form output
			$ftp_form = ob_get_clean();

			// Do not show FTP form
			if ( $silent ) {
				$this->credentials_form = $ftp_form;
			} // Show FTP form
			elseif ( ! $creds ) {
				echo $ftp_form;
			}
		}

		return $creds;
	}

	/**
	 * Get credentials form.
	 *
	 * @return string
	 */
	public function get_credentials_form() {
		return $this->credentials_form;
	}

	/**
	 * Replace abs path with remote file abs path.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function real_abspath( $file ) {
		return str_replace( ABSPATH, $this->abspath(), $file );
	}

	/**
	 * Reference to WP_Filesystem global.
	 *
	 * @return WP_Filesystem_Base|WP_Error
	 * @global WP_Filesystem_Base $wp_filesystem
	 */
	public function wp() {
		global $wp_filesystem;

		// Error when filesystem global is not set up
		if ( ! $this->ok ) {
			return $this->error_not_initialized();
		}

		return $wp_filesystem;
	}

	/**
	 * Unzips a specified ZIP file to a location on the filesystem.
	 *
	 * @param string $file
	 * @param string $to
	 *
	 * @return true|WP_Error
	 */
	public function unzip_file( $file, $to ) {

		// Error when filesystem global is not set up
		if ( ! $this->ok ) {
			return $this->error_not_initialized();
		}

		return unzip_file( $file, $this->real_abspath( $to ) );
	}

	/**
	 * Compress a file or directory with WordPress PCLZIP library.
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return true|WP_Error
	 */
	public function zip_file( $source, $destination = '' ) {

		// Set the mbstring internal encoding to a binary safe encoding
		mbstring_binary_safe_encoding();

		// Optional destination path name generate
		if ( ! $destination ) {
			$destination = dirname( $source ) . DIRECTORY_SEPARATOR . basename( $source ) . '.zip';
		} elseif ( '.' === dirname( $destination ) ) {
			$destination = dirname( $source ) . DIRECTORY_SEPARATOR . $destination;
		}

		// Add zip extension if not present
		if ( ! preg_match( '/\.zip$/i', $destination ) ) {
			$destination .= '.zip';
		}

		// Absolute destination path
		$destination = $this->real_abspath( $destination );

		// Load class file if it's not loaded yet
		if ( ! class_exists( 'PclZip' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		}

		// Creative archive
		$archive = new PclZip( wp_normalize_path( $destination ) );
		$result  = $archive->add( wp_normalize_path( $source ), PCLZIP_OPT_REMOVE_PATH, dirname( $source ) );

		// Reset the mbstring internal encoding
		reset_mbstring_encoding();

		// Creating archive failed
		if ( 0 === $result ) {
			return new WP_Error( 'kalium_fs_zip_failed', $archive->error_string );
		}

		return true;
	}

	/**
	 * Copies a directory from one location to another via the WordPress Filesystem Abstraction.
	 *
	 * @param string   $from
	 * @param string   $to
	 * @param string[] $skip_list
	 *
	 * @return true|WP_Error
	 */
	public function copy_dir( $from, $to, $skip_list = [] ) {
		return copy_dir( $this->real_abspath( $from ), $this->real_abspath( $to ), $skip_list );
	}
}
