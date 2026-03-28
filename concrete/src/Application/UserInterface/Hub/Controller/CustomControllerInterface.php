<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Hub\Importer\ImporterInterface;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\UrlInterface;

interface CustomControllerInterface extends ControllerInterface
{
    public function getLabel(): string;

    public function getHomePageUrl(): string;
}
