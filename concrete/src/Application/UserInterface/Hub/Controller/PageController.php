<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\UrlInterface;

class PageController implements ControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getMenuTitle(): string
    {
        return t('Pages');
    }

    public function getHomePageUrl(): UrlInterface
    {
        return app('url/resolver/path')
            ->resolve(['/dashboard/sitemap/full']);
    }
}
