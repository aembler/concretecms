<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;

interface HubInterface
{
    public function getController(): ControllerInterface;

    public function getLabel(): string;

    public function getHomePageUrl(): string;

}
