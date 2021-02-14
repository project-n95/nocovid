<?php

namespace WPStaging\Pro\Staging;

class PluginUpdates
{
    /**
     * Do not show update notifications for WP STAGING Pro on the staging site
     *
     * @filter site_transient_update_plugins
     */
    public function disablePluginUpdateChecksOnStagingSite($value)
    {
        if (isset($value->response['wp-staging-pro/wp-staging-pro.php'])) {
            unset($value->response['wp-staging-pro/wp-staging-pro.php']);
        }

        return $value;
    }
}
