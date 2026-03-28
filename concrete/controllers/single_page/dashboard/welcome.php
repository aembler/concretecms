<?php

namespace Concrete\Controller\SinglePage\Dashboard;

use Concrete\Core\Application\UserInterface\Hub\HubIdentifier;
use Concrete\Core\Feature\Features;
use Concrete\Core\Page\Controller\DashboardPageController;

class Welcome extends DashboardPageController
{
    public function view()
    {
        $this->setThemeViewTemplate('desktop.php');
    }

    public function getHub(): ?HubIdentifier
    {
        return new HubIdentifier(Features::DESKTOP);
    }
}
