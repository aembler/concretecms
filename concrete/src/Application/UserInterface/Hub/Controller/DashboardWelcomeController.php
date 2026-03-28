<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\CustomImporter;
use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\InlineSvgIcon;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Page\Page;

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

    public function getIcon(): IconInterface
    {
        return new InlineSvgIcon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M9.75 20v-5.25c0-.69.56-1.25 1.25-1.25h2c.69 0 1.25.56 1.25 1.25V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>');
    }

    public function getImporter(): ImporterInterface
    {
        return new CustomImporter('dashboard_welcome');
    }
}
