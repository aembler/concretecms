<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\CustomImporter;
use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Url\UrlInterface;

class DashboardWelcomeController implements CustomControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getLabel(): string
    {
        return t('Overview');
    }

    public function getHomePageUrl(): string
    {
        $page = Page::getByPath('/dashboard/welcome');
        return $page->getCollectionLink();
    }

    public function getImporter(): ImporterInterface
    {
        return new CustomImporter('dashboard_welcome');
    }
}
