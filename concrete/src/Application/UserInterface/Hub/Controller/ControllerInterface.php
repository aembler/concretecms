<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Navigation\NavigationInterface;

interface ControllerInterface
{
    public function getNavigation(): NavigationInterface;
}
