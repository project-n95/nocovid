<?php

namespace WPStaging\Bootstrap\V1;

use WPStaging\Core\WPStaging;

if (!class_exists(WpstgBootstrap::class)) {
    abstract class WpstgBootstrap
    {
        private $shouldBootstrap = true;
        private $rootPath;
        private $entryPoint;
        private $requirements;

        /**
         * WpstgBootstrap constructor.
         *
         * @param string            $entryPoint   The main plugin file.
         * @param WpstgRequirements $requirements The concrete instance of the requirements.
         */
        public function __construct($entryPoint, WpstgRequirements $requirements)
        {
            $this->entryPoint   = $entryPoint;
            $this->rootPath     = dirname($entryPoint);
            $this->requirements = $requirements;
        }

        /**
         * Free and Pro implement their own actions after bootstrap.
         *
         * @return void
         */
        abstract protected function afterBootstrap();

        public function checkRequirements()
        {
            try {
                $this->requirements->checkRequirements();
            } catch (\Exception $e) {
                $this->shouldBootstrap = false;

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf("[Activation] WP STAGING: %s", $e->getMessage()));
                }
            }
        }

        public function passedRequirements()
        {
            return $this->shouldBootstrap;
        }

        public function bootstrap()
        {
            // Early bail: Requirements not met.
            if (!$this->shouldBootstrap) {
                return;
            }

            // Only register the autoloader once we know we should bootstrap.
            if (file_exists(__DIR__ . '/../autoloader_dev.php')) {
                require_once __DIR__ . '/../autoloader_dev.php';
            } else {
                require_once __DIR__ . '/../autoloader.php';
            }

            $this->registerCommonConstants();
            $this->afterBootstrap();
            $this->run();
        }

        /**
         * Constants that apply for both Free and Pro
         */
        private function registerCommonConstants()
        {
            if (!defined('WPSTG_PLUGIN_FILE')) {
                /*
                 * This is the same constant for both Free and Pro.
                 * That's what differs it from WPSTG_FREE_ENTRYPOINT and
                 * WPSTG_PRO_ENTRYPOINT.
                 */
                define('WPSTG_PLUGIN_FILE', $this->entryPoint);
            }

            // Absolute path to plugin dir /var/www/.../plugins/wp-staging(-pro)
            if (!defined('WPSTG_PLUGIN_DIR')) {
                define('WPSTG_PLUGIN_DIR', plugin_dir_path(WPSTG_PLUGIN_FILE));
            }

            // URL of the base folder
            if (!defined('WPSTG_PLUGIN_URL')) {
                define('WPSTG_PLUGIN_URL', plugin_dir_url(WPSTG_PLUGIN_FILE));
            }

            // Expected version number of the must-use plugin 'optimizer'. Used for automatic updates of the mu-plugin
            if (!defined('WPSTG_OPTIMIZER_MUVERSION')) {
                define('WPSTG_OPTIMIZER_MUVERSION', 1.4);
            }

            if (!defined('WPSTG_PLUGIN_SLUG')) {
                // /var/www/single/wp-content/plugins/wp-staging-pro/wp-staging-pro.php => wp-staging-pro
                define('WPSTG_PLUGIN_SLUG', basename(dirname(WPSTG_PLUGIN_FILE)));
            }

            if (!defined('WPSTG_PLUGIN_DOMAIN')) {
                // An identifier that is the same both for WPSTAGING Free and WPSTAGING Pro
                define('WPSTG_PLUGIN_DOMAIN', 'wp-staging');
            }
        }

        /**
         * Everything is ready. Let's run it!
         */
        private function run()
        {
            $wpStaging = WPStaging::getInstance();

            /*
             * Set the WPSTG_COMPATIBLE constant in the container,
             * so that we can change it for testing purposes.
             */
            $wpStaging->set('WPSTG_COMPATIBLE', WPSTG_COMPATIBLE);

            // Wordpress DB Object
            global $wpdb;

            if ($wpdb instanceof \wpdb) {
                $wpStaging->set("wpdb", $wpdb);
            }
        }
    }
}

