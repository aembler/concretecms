<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;

class ExpressController implements ControllerInterface
{
    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }
}
