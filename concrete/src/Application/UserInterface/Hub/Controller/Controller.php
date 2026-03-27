<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;

class Controller implements ControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }
}
