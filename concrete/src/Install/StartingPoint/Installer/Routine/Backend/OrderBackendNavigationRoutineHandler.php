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
     * The canonical display order for known /dashboard/system pages.
     */
    protected const SYSTEM_DASHBOARD_PAGE_ORDER = [
        '/dashboard/system/basics',
        '/dashboard/system/social',
        '/dashboard/system/express',
        '/dashboard/system/multisite',
        '/dashboard/system/multilingual',
        '/dashboard/system/files',
        '/dashboard/system/automation',
        '/dashboard/system/notification',
        '/dashboard/system/optimization',
        '/dashboard/system/permissions',
        '/dashboard/system/registration',
        '/dashboard/system/mail',
        '/dashboard/system/calendar',
        '/dashboard/system/boards',
        '/dashboard/system/conversations',
        '/dashboard/system/attributes',
        '/dashboard/system/update',
        '/dashboard/system/api',
    ];

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function getChildPages(Page $page): array
    {
        $pages = [];
        foreach ($page->getCollectionChildrenArray() as $pageID) {
            $child = Page::getByID($pageID, 'RECENT');
            if (!$child || $child->isError()) {
                continue;
            }
            $pages[] = $child;
        }

        return $pages;
    }

    protected function getPageOrderLookup(array $orderedPaths): array
    {
        return array_flip($orderedPaths);
    }

    protected function reorderChildPages(Page $parentPage, array $orderedPaths): void
    {
        $pages = $this->getChildPages($parentPage);
        $orderLookup = $this->getPageOrderLookup($orderedPaths);

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

        $parentPage->rescanChildrenDisplayOrder();
    }

    public function __invoke()
    {
        $dashboard = Page::getByPath('/dashboard', 'RECENT');
        if (!$dashboard || $dashboard->isError()) {
            return;
        }

        $this->reorderChildPages($dashboard, static::DASHBOARD_PAGE_ORDER);

        $system = Page::getByPath('/dashboard/system', 'RECENT');
        if ($system && !$system->isError()) {
            $this->reorderChildPages($system, static::SYSTEM_DASHBOARD_PAGE_ORDER);
        }
    }
}
