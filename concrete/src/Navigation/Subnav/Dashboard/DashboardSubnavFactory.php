<?php

namespace Concrete\Core\Navigation\Subnav\Dashboard;

use Concrete\Core\Application\Service\Dashboard;
use Concrete\Core\Navigation\Item\Item;
use Concrete\Core\Page\Page;

class DashboardSubnavFactory
{
    /**
     * @var \Concrete\Core\Application\Service\Dashboard
     */
    protected $dashboard;

    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function getSubnav(Page $dashboardPage): DashboardSubnav
    {
        $subnav = new DashboardSubnav();
        $parentPage = $this->getSubnavParentPage($dashboardPage);

        foreach ($parentPage->getCollectionChildren('ACTIVE') as $childPage) {
            if ($childPage->getAttribute('exclude_nav')) {
                continue;
            }
            $subnav->add(new Item(
                $childPage->getCollectionLink(),
                t($childPage->getCollectionName()),
                $childPage->getCollectionID() === $dashboardPage->getCollectionID()
            ));
        }

        return $subnav;
    }

    protected function getSubnavParentPage(Page $dashboardPage): Page
    {
        if ($dashboardPage->getCollectionPath() === '/dashboard') {
            return $dashboardPage;
        }

        $parentPage = Page::getByID($dashboardPage->getCollectionParentID(), 'ACTIVE');
        if ($parentPage instanceof Page && !$parentPage->isError() && $this->dashboard->inDashboard($parentPage)) {
            return $parentPage;
        }

        return $dashboardPage;
    }
}
