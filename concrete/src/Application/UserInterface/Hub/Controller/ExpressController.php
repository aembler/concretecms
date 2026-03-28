<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\ExpressImporter;
use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\Url;
use Concrete\Core\Url\UrlInterface;

class ExpressController implements ControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getHomePageUrl(): UrlInterface
    {
        return Url::createFromUrl('https://www.yahoo.com');
    }

    public function getImporter(): ImporterInterface
    {
        return app(ExpressImporter::class);
    }
}
