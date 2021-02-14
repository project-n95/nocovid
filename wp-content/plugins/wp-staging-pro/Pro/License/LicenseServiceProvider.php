<?php

namespace WPStaging\Pro\License;

use WPStaging\Backend\Pro\Licensing\Licensing;
use WPStaging\Backend\Pro\Licensing\Version;
use WPStaging\Framework\DI\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    public function registerClasses()
    {
        $this->initializeLegacy();
    }

    public function addHooks()
    {
        // no-op
    }

    /**
     * Initialize legacy classes
     *
     * Allow executing cron jobs by regular frontpage visitors
     */
    private function initializeLegacy()
    {
        $this->container->make(Licensing::class);
        $this->container->make(Version::class);
    }
}
