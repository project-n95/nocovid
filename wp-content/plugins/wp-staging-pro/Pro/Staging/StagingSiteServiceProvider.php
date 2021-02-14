<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Framework\DI\ServiceProvider;

class StagingSiteServiceProvider extends ServiceProvider
{
    public function registerClasses()
    {
        //no-op
    }

    public function addHooks()
    {
        add_filter('site_transient_update_plugins', [$this->container->make(PluginUpdates::class), 'disablePluginUpdateChecksOnStagingSite'], 10, 1);
    }
}
