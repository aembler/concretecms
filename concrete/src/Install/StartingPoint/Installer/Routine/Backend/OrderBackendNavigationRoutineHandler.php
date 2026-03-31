<?php

namespace Concrete\Core\Install\StartingPoint\Installer\Routine\Backend;

use Concrete\Core\Application\Application;
use Concrete\Core\Page\Page;

class OrderBackendNavigationRoutineHandler
{
    /**
     * The canonical display order for known top-level dashboard pages.
     */
    protected const DASHBOARD_PAGE_ORDER = [
        '/dashboard/welcome',
        '/dashboard/sitemap',
        '/dashboard/files',
        '/dashboard/users',
        '/dashboard/blocks',
        '/dashboard/reports',
        '/dashboard/design',
        '/dashboard/calendar',
        '/dashboard/conversations',
        '/dashboard/boards',
        '/dashboard/express',
        '/dashboard/extend',
        '/dashboard/system',
    ];

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function getTopLevelDashboardPages(Page $dashboard): array
    {
        $pages = [];
        foreach ($dashboard->getCollectionChildrenArray() as $pageID) {
            $page = Page::getByID($pageID, 'RECENT');
            if (!$page || $page->isError()) {
                continue;
            }
            $pages[] = $page;
        }

        return $pages;
    }

    protected function getDashboardPageOrderLookup(): array
    {
        return array_flip(static::DASHBOARD_PAGE_ORDER);
    }

    public function __invoke()
    {
        $dashboard = Page::getByPath('/dashboard', 'RECENT');
        if (!$dashboard || $dashboard->isError()) {
            return;
        }

        $pages = $this->getTopLevelDashboardPages($dashboard);
        $orderLookup = $this->getDashboardPageOrderLookup();

        $knownPages = [];
        $unknownPages = [];

        foreach ($pages as $page) {
            $path = $page->getCollectionPath();
            if (array_key_exists($path, $orderLookup)) {
                $knownPages[] = $page;
            } else {
                $unknownPages[] = $page;
            }
        }

        usort($knownPages, static function (Page $a, Page $b) use ($orderLookup): int {
            return $orderLookup[$a->getCollectionPath()] <=> $orderLookup[$b->getCollectionPath()];
        });

        $displayOrder = 0;
        foreach (array_merge($knownPages, $unknownPages) as $page) {
            $page->updateDisplayOrder($displayOrder);
            $displayOrder++;
        }

        $dashboard->rescanChildrenDisplayOrder();
    }
}
