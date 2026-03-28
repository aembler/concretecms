<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Application\UserInterface\Hub\Importer\PageImporter;
use Concrete\Core\Entity\Hub\PageHub;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;

class PageController implements ControllerInterface
{

    public function __construct(
        public PageHub $hub
    ) {}
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getImporter(): ImporterInterface
    {
        return app(PageImporter::class);
    }
}
