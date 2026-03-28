<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Application\UserInterface\Hub\Importer\PageImporter;
use Concrete\Core\Entity\Hub\PageHub;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Url\UrlInterface;

class PageController implements ControllerInterface
{

    public function __construct(
        public PageHub $hub
    ) {}
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getMenuTitle(): string
    {
        return $this->hub->getLabel();
    }

    public function getHomePageUrl(): UrlInterface
    {
        $pathResolver = app('url/resolver/path');
        $pageResolver = app('url/resolver/page');
        $page = $this->hub->getPage();
        if ($page) {
            return $pageResolver->resolve([$page]);
        }

        return $pathResolver->resolve(['/dashboard']);
    }

    public function getImporter(): ImporterInterface
    {
        return app(PageImporter::class);
    }
}
