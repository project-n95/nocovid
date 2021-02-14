<?php
/**
 * Kalium WordPress Theme
 *
 * Custom Theme CSS.
 *
 * @version 2.0
 *
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Custom_CSS {

	/**
	 * Menu ID.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'laborator_custom_css';

	/**
	 * Get custom CSS.
	 *
	 * @param string|null $type
	 *
	 * @return string
	 */
	public static function get( $type = null ) {
		if ( in_array( $type, [ 'lg', 'md', 'sm', 'xs' ] ) || ( is_admin() && in_array( $type, [
					'less',
					'sass'
				] ) ) ) {
			return get_option( sprintf( 'laborator_custom_css_%s', $type ) );
		} else if ( in_array( $type, [ 'less', 'sass' ] ) ) {
			return get_option( sprintf( 'laborator_custom_css_%s_compiled', $type ) );
		}

		return get_option( 'laborator_custom_css' );
	}

	/**
	 * Set custom CSS.
	 *
	 * @param string      $css
	 * @param string|null $type
	 *
	 * @return void
	 */
	public static function set( $css, $type = '' ) {

		// Option
		$option_name = 'laborator_custom_css';

		if ( in_array( $type, [ 'lg', 'md', 'sm', 'xs', 'less', 'sass' ] ) ) {
			$option_name = sprintf( 'laborator_custom_css_%s', $type );
		}

		// Necessary to support quotes
		$css = wp_unslash( $css );

		/**
		 * Compile Less Instantly.
		 *
		 * @deprecated 3.0 Kalium will no longer compile LESS in the upcoming updates and this option will be removed.
		 */
		if ( 'less' === $type ) {
			if ( ! class_exists( 'lessc' ) ) {
				require_once( __DIR__ . '/includes/lessc.inc.php' );
			}

			try {
				$less          = new lessc;
				$compiled_less = $less->compile( $css );
				update_option( "laborator_custom_css_{$type}_compiled", $compiled_less );
			} catch ( Exception $e ) {
			}
		}

		/**
		 * Compile Sass Instantly.
		 *
		 * @deprecated 3.0 Kalium will no longer compile SASS in the upcoming updates and this option will be removed.
		 */
		if ( 'sass' === $type ) {
			if ( ! class_exists( 'sassc' ) ) {
				require_once( __DIR__ . '/includes/scss.inc.php' );
			}

			try {
				$scss          = new scssc;
				$compiled_sass = $scss->compile( $css );
				update_option( "laborator_custom_css_{$type}_compiled", $compiled_sass );
			} catch ( Exception $e ) {
			}
		}

		// Update CSS value
		update_option( $option_name, $css );
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Hooks
		add_action( 'admin_menu', [ $this, '_admin_menu' ] );
		add_action( 'wp_head', [ $this, '_custom_css_frontend' ] );
	}

	/**
	 * Set active status of the plugin.
	 *
	 * @param bool $enabled
	 *
	 * @return void
	 */
	public function set_status( $enabled ) {
		update_option( 'laborator_custom_css_status', $enabled ? 1 : 0 );
	}

	/**
	 * Custom CSS as menu item.
	 *
	 * @return void
	 */
	public function _admin_menu() {
		add_menu_page( 'Custom CSS', 'Custom CSS', 'edit_theme_options', self::MENU_SLUG, [
			$this,
			'_custom_css_edit_page'
		], 'dashicons-editor-code' );

		// Disabled custom CSS notice
		if ( false === boolval( get_option( 'laborator_custom_css_status', 1 ) ) && ( self::MENU_SLUG === kalium()->request->query( 'page' ) && 'settings' !== kalium()->request->query( 'tab' ) ) ) {
			$settings_page_url = esc_url( admin_url( sprintf( 'admin.php?page=%s&tab=settings', self::MENU_SLUG ) ) );
			kalium()->helpers->add_admin_notice( sprintf( 'Custom CSS is currently disabled. You can change this <a href="%s">here &raquo;</a>', $settings_page_url ), 'warning' );
		}

		// Deprecated LESS and SASS
        if ( self::MENU_SLUG === kalium()->request->query( 'page' ) && in_array( kalium()->request->query( 'tab' ), [ 'less', 'sass' ] ) ) {
			kalium()->helpers->add_admin_notice( 'We discourage to use LESS/SASS feature as it is deprecated and will be removed in future updates of the theme. Please add your custom CSS in <strong>General Style</strong> or <strong>Responsive</strong> tab.', 'warning' );
        }

	}

	/**
	 * Page content for Custom CSS.
	 *
	 * @return void
	 */
	public function _custom_css_edit_page() {

		/**
		 * Save custom CSS.
		 */
		foreach ( [ '', 'lg', 'md', 'sm', 'xs', 'less', 'sass' ] as $type ) {
			$input_name = $type ? "laborator_custom_css_{$type}" : 'laborator_custom_css';

			if ( kalium()->request->has( $input_name, 'post' ) ) {
				self::set( kalium()->request->input( $input_name ), $type );
				$success = true;
			}
		}

		// Custom CSS status
		if ( isset( $_POST['custom_css_status'] ) ) {
			check_admin_referer( 'laborator-custom-css-settings' );
			$this->set_status( $_POST['custom_css_status'] );
			$success = true;
		}

		if ( isset( $success ) ) {
			?>
            <div class="updated">
                <p>
                    <strong>Changes have been saved.</strong>
                </p>
            </div>
			<?php
		}

		// Custom CSS vars
		$custom_css    = self::get();
		$custom_css_lg = self::get( 'lg' );
		$custom_css_md = self::get( 'md' );
		$custom_css_sm = self::get( 'sm' );
		$custom_css_xs = self::get( 'xs' );

		// Soon to be deprecated
		$custom_css_less = self::get( 'less' );
		$custom_css_sass = self::get( 'sass' );

		// Page and tab
		$tab = kalium()->request->query( 'tab' );

		// Current tab
		$current_tab = 'main';
		$type        = 'text/css';

		switch ( $tab ) {
			case 'responsive':
				$current_tab = 'responsive';
				break;

			case 'less':
				$current_tab = 'less';
				$type        = 'text/x-less';
				break;

			case 'sass':
				$current_tab = 'sass';
				$type        = 'text/x-sass';
				break;

			case 'settings':
				$current_tab = 'settings';
				break;
		}

		// Enqueue editor
		$editor_settings = wp_enqueue_code_editor( [
			'type' => $type,
		] );
		?>
        <script type="text/javascript">
			var kaliumCodeEditor = function ( selector ) {
				jQuery( selector ).each( function ( i, textarea ) {
					jQuery( textarea ).attr( 'placeholder', '' );
					wp.codeEditor.initialize( textarea, <?php echo wp_json_encode( $editor_settings ); ?> );
				} );
			};
        </script>

        <style>
            .code-editor-textarea {
                position: relative;
                border: 1px solid #ddd;
                margin: 0;
                margin-bottom: 20px;
            }

            .code-editor-textarea .CodeMirror {
                height: auto;
            }

            .code-editor-textarea .ace_editor {
                margin: 0;
            }

            .code-editor-textarea .ace_editor ~ textarea {
                display: none;
            }

            .code-editor-textarea .ace_editor,
            .code-editor-textarea textarea {
                min-height: 600px;
            }

            .code-editor-textarea .CodeMirror .CodeMirror-scroll {
                min-height: 500px;
            }

            .code-editor-textarea--small .ace_editor,
            .code-editor-textarea--small textarea {
                min-height: 200px;
            }

            .code-editor-textarea--small .CodeMirror .CodeMirror-scroll {
                min-height: 200px;
            }
        </style>

        <div class="wrap">

            <h2 id="main-title">Custom CSS</h2>

            <nav class="nav-tab-wrapper wp-clearfix">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'main' ) ); ?>" class="nav-tab<?php echo 'main' === $current_tab ? ' nav-tab-active' : ''; ?>">General
                    Style</a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'responsive' ) ); ?>" class="nav-tab<?php echo 'responsive' === $current_tab ? ' nav-tab-active' : ''; ?>">Responsive</a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'less' ) ); ?>" class="nav-tab<?php echo 'less' === $current_tab ? ' nav-tab-active' : ''; ?>">LESS</a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'sass' ) ); ?>" class="nav-tab<?php echo 'sass' === $current_tab ? ' nav-tab-active' : ''; ?>">SASS</a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>" class="nav-tab<?php echo 'settings' === $current_tab ? ' nav-tab-active' : ''; ?>">Settings</a>
            </nav>

			<?php if ( 'main' === $current_tab ): ?>
                <h3>Apply your own stylesheet here</h3>

                <form method="post">
                    <div class="code-editor-textarea">
                        <textarea class="large-text code" id="laborator_custom_css" name="laborator_custom_css" rows="10" placeholder="Loading code editor..."><?php echo $custom_css; ?></textarea>
                    </div>
                    <button type="submit" class="button button-primary save" name="save_changes">Save Changes</button>
                </form>

                <script type="text/javascript">
					jQuery( document ).ready( function () {
						kaliumCodeEditor( '#laborator_custom_css' );
					} );
                </script>

			<?php elseif ( 'responsive' === $current_tab ) : ?>
                <h3>Targeting custom screen sizes</h3>

                <form method="post">
                    <h4>
                        <small>
                            Minimum Screen Size: <strong>1200px</strong>
                        </small>

                        LG - Large Screen
                    </h4>

                    <div class="code-editor-textarea code-editor-textarea--small">
                    <textarea class="large-text code" id="laborator_custom_css_lg" name="laborator_custom_css_lg"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_lg; ?></textarea>
                    </div>


                    <h4>
                        <small>
                            Minimum Screen Size: <strong>992px</strong>
                        </small>

                        MD - Medium Screen
                    </h4>

                    <div class="code-editor-textarea code-editor-textarea--small">
                    <textarea class="large-text code" id="laborator_custom_css_md" name="laborator_custom_css_md"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_md; ?></textarea>
                    </div>


                    <h4>
                        <small>
                            Minimum Screen Size: <strong>768px</strong>
                        </small>

                        SM - Small Screen
                    </h4>

                    <div class="code-editor-textarea code-editor-textarea--small">
                    <textarea class="large-text code" id="laborator_custom_css_sm" name="laborator_custom_css_sm"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_sm; ?></textarea>
                    </div>

                    <h4>
                        <small>
                            Maximum Screen Size: <strong>768px</strong>
                        </small>

                        XS - Extra Small Screen
                    </h4>

                    <div class="code-editor-textarea code-editor-textarea--small">
                    <textarea class="large-text code" id="laborator_custom_css_xs" name="laborator_custom_css_xs"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_xs; ?></textarea>
                    </div>

                    <script type="text/javascript">
						jQuery( document ).ready( function () {
							kaliumCodeEditor( '#laborator_custom_css_lg' );
							kaliumCodeEditor( '#laborator_custom_css_md' );
							kaliumCodeEditor( '#laborator_custom_css_sm' );
							kaliumCodeEditor( '#laborator_custom_css_xs' );
						} );
                    </script>


                    <button type="submit" class="button button-primary save" name="save_changes">Save Changes</button>
                </form>
			<?php elseif ( 'less' === $current_tab ) : ?>
                <h3>Apply your own style in <a href="http://www.lesscss.org/" target="_blank">LESS</a> language</h3>

                <form method="post">
                    <div class="code-editor-textarea">
                    <textarea class="large-text code" id="laborator_custom_css_less" name="laborator_custom_css_less"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_less; ?></textarea>
                    </div>
                    <button type="submit" class="button button-primary save" name="save_changes">Save Changes</button>
                </form>

                <script type="text/javascript">
					window.onload = function () {
						kaliumCodeEditor( '#laborator_custom_css_less' );
					}
                </script>
			<?php elseif ( 'sass' === $current_tab ) : ?>
                <h3>Apply your own style in <a href="http://sass-lang.com/" target="_blank">SASS</a> language</h3>

                <form method="post">
                    <div class="code-editor-textarea">
                    <textarea class="large-text code" id="laborator_custom_css_sass" name="laborator_custom_css_sass"
                              rows="10" placeholder="Loading code editor..."><?php echo $custom_css_sass; ?></textarea>
                    </div>
                    <button type="submit" class="button button-primary save" name="save_changes">Save Changes</button>
                </form>

                <script type="text/javascript">
					jQuery( document ).ready( function () {
						kaliumCodeEditor( '#laborator_custom_css_sass' );
					} );
                </script>
			<?php elseif ( 'settings' === $current_tab ) : ?>
                <form method="post">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                Status
                            </th>
                            <td>
                                <select name="custom_css_status">
                                    <option value="1">Enable</option>
                                    <option value="0" <?php selected( get_option( 'laborator_custom_css_status', 1 ), 0, true ); ?>>
                                        Disable
                                    </option>
                                </select>

                                <p class="description">You can disable Custom CSS for debugging purposes.</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>

					<?php wp_nonce_field( 'laborator-custom-css-settings' ); ?>

					<?php submit_button(); ?>
                </form>
			<?php endif; ?>

            <?php if ( 'settings' !== $tab ) : ?>
            <p class="footer">
                * The CSS written here won't be lost when you update the theme or switch to child theme and vice versa.
                <br/>
                * If the CSS here is not applied, consider adding <code>!important</code> after property value to
                overwrite
                the default
                value set by the theme, for example: <code>font-size: 18px <strong>!important</strong></code>.
            </p>
            <?php endif; ?>

            <style>
                #main-title {
                    margin-left: 10px;
                    margin-bottom: 5px;
                }

                form h4 {
                    margin: 0;
                    padding: 5px 15px;
                    text-transform: uppercase;
                    background: #fff;
                    border: 1px solid #e0e0e0;
                }

                form h4 small {
                    float: right;
                    color: #999;
                }

                form h4 small strong {
                    color: #111;
                    text-decoration: underline;
                }

                form textarea + h4 {
                    margin-top: 25px !important;
                }

                .wp-core-ui .button-primary.save {
                    margin-top: 15px;
                }

                .updated {
                    margin-top: 15px !important;
                }

                p.footer {
                    margin-top: 30px;
                    margin-bottom: 25px;
                    font-size: 11px;
                    color: #777;
                    width: 100%;
                }

                p.footer code {
                    font-size: 11px;
                }
            </style>
        </div>
		<?php
	}

	/**
	 * Print styles on frontend.
	 *
	 * @return void
	 */
	public function _custom_css_frontend() {

		// Do not run when plugin status is disabled
		if ( ! wp_validate_boolean( get_option( 'laborator_custom_css_status', 1 ) ) ) {
			return;
		}

		// Device sizes
		$screen_lg = 1200;
		$screen_md = 992;
		$screen_sm = 768;
		$screen_xs = 480;

		// Custom CSS vars
		$custom_css    = self::get();
		$custom_css_lg = self::get( 'lg' );
		$custom_css_md = self::get( 'md' );
		$custom_css_sm = self::get( 'sm' );
		$custom_css_xs = self::get( 'xs' );

		// Soon to deprecate
		$custom_css_less = self::get( 'less' );
		$custom_css_sass = self::get( 'sass' );

		// CSS to append
		$custom_css_append = [];

		if ( $custom_css ) {
			$custom_css_append[] = $custom_css;
		}

		// XS - Media Screen CSS
		if ( $custom_css_xs ) {
			$custom_css_append[] = "@media screen and (max-width: {$screen_sm}px){";
			$custom_css_append[] = $custom_css_xs;
			$custom_css_append[] = '}';
		}

		// SM - Media Screen CSS
		if ( $custom_css_sm ) {
			$custom_css_append[] = "@media screen and (min-width: {$screen_sm}px){";
			$custom_css_append[] = $custom_css_sm;
			$custom_css_append[] = '}';
		}

		// MD - Media Screen CSS
		if ( $custom_css_md ) {
			$custom_css_append[] = "@media screen and (min-width: {$screen_md}px){";
			$custom_css_append[] = $custom_css_md;
			$custom_css_append[] = '}';
		}

		// LG - Media Screen CSS
		if ( $custom_css_lg ) {
			$custom_css_append[] = "@media screen and (min-width: {$screen_lg}px){";
			$custom_css_append[] = $custom_css_lg;
			$custom_css_append[] = '}';
		}

		// LESS CSS
		if ( $custom_css_less ) {
			$custom_css_append[] = $custom_css_less;
		}

		// SASS CSS
		if ( $custom_css_sass ) {
			$custom_css_append[] = $custom_css_sass;
		}

		// Append custom CSS
		if ( ! empty( $custom_css_append ) ) {
			echo sprintf( '<style id="theme-custom-css">%s</style>', kalium_compress_text( implode( PHP_EOL, $custom_css_append ) ) );
		}
	}
}

/**
 * Get custom CSS with optional extension.
 *
 * @param string|null $ex
 *
 * @return string
 *
 * @deprecated 3.0
 * @see        Kalium_Custom_CSS::get()
 */
function laborator_get_custom_css( $ex = '' ) {
	return Kalium_Custom_CSS::get( $ex );
}

/**
 * Set custom CSS with optional extension.
 *
 * @param string $css
 * @param string $ex
 *
 * @return void
 *
 * @deprecated 3.0
 * @see        Kalium_Custom_CSS::set()
 */
function laborator_set_custom_css( $css, $ex = '' ) {
	Kalium_Custom_CSS::set( $css, $ex );
}

// Create instance
new Kalium_Custom_CSS();
