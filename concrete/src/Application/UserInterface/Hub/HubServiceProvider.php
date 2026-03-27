<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\Manager;
use Concrete\Core\Foundation\Service\Provider;

class HubServiceProvider extends Provider
{
    public function register()
    {
        $this->app->singleton(Manager::class);
    }
}