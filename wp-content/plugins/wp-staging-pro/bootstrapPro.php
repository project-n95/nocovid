<?php

namespace WPStaging\Bootstrap\V1;

require_once __DIR__ . '/Bootstrap/V1/Requirements/WpstgProRequirements.php';
require_once __DIR__ . '/Bootstrap/V1/WpstgBootstrap.php';

if (!class_exists(WpstgProBootstrap::class)) {
    class WpstgProBootstrap extends WpstgBootstrap
    {
        protected function afterBootstrap()
        {
            // WP STAGING version number
            if (!defined('WPSTGPRO_VERSION')) {
                define('WPSTGPRO_VERSION', '3.1.9');
            }

            // Compatible up to WordPress Version
            if (!defined('WPSTG_COMPATIBLE')) {
                define('WPSTG_COMPATIBLE', '5.6');
            }
        }
    }
}

$bootstrap = new WpstgProBootstrap(WPSTG_PRO_ENTRYPOINT, new WpstgProRequirements(WPSTG_PRO_ENTRYPOINT));

// Pro requirement-checking runs after Free requirement-checking.
add_action('plugins_loaded', [$bootstrap, 'checkRequirements'], 6);
add_action('plugins_loaded', [$bootstrap, 'bootstrap'], 10);

/** Installation Hooks */
if (!class_exists('WPStaging\Install')) {
    require_once __DIR__ . "/install.php";

    $install = new \WPStaging\Install($bootstrap);
    register_activation_hook(WPSTG_PRO_ENTRYPOINT, [$install, 'activation']);
}
