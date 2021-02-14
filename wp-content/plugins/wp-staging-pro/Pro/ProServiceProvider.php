<?php

namespace WPStaging\Pro;

use WPStaging\Framework\DI\Container;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\License\LicenseServiceProvider;
use WPStaging\Pro\Snapshot\SnapshotServiceProvider;
use WPStaging\Pro\Staging\StagingSiteServiceProvider;
use WPStaging\Pro\Template\TemplateServiceProvider;

class ProServiceProvider extends ServiceProvider
{
    /** @var Container $container */
    protected $container;

    public function registerClasses()
    {
        $this->container->register(TemplateServiceProvider::class);
        $this->container->register(LicenseServiceProvider::class);
        $this->container->register(SnapshotServiceProvider::class);

        if ($this->container->make(SiteInfo::class)->isStaging()) {
            $this->container->register(StagingSiteServiceProvider::class);
        }
    }

    public function addHooks()
    {
        //no-op
    }
}
