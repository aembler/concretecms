<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;

interface HubInterface
{
    public function getController(): ControllerInterface;

    public function getLabel(): string;

    public function getHomePageUrl(): string;

    public function getIcon(): IconInterface;
}
