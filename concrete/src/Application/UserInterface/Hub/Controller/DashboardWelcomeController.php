<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\CustomImporter;
use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\UrlInterface;

class DashboardWelcomeController implements ControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getMenuTitle(): string
    {
        return t('Overview');
    }

    public function getHomePageUrl(): UrlInterface
    {
        return app('url/resolver/path')
            ->resolve(['/dashboard/welcome']);
    }

    public function getImporter(): ImporterInterface
    {
        return new CustomImporter('dashboard_welcome');
    }
}
