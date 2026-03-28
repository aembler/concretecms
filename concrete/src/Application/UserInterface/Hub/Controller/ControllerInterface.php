<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\UrlInterface;

interface ControllerInterface
{
    public function getNavigation(): NavigationInterface;

    public function getMenuTitle(): string;

    public function getHomePageUrl(): UrlInterface;

    public function getImporter(): ImporterInterface;
}
