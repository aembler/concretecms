<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\UserInterface\Icon\IconInterface;

interface CustomControllerInterface extends ControllerInterface
{
    public function getLabel(): string;

    public function getHomePageUrl(): string;

    public function getIcon(): IconInterface;
}
