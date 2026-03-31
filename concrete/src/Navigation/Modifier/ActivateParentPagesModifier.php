<?php
namespace Concrete\Core\Navigation\Modifier;

use Concrete\Core\Html\Service\Navigation as NavigationService;
use Concrete\Core\Navigation\Item\PageItem;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Page\Page;

class ActivateParentPagesModifier implements ModifierInterface
{
    /**
     * @var NavigationService
     */
    protected $navigationService;

    /**
     * @var Page
     */
    protected $currentPage;

    public function __construct(NavigationService $navigationService, ?Page $currentPage = null)
    {
        $this->navigationService = $navigationService;
        $this->currentPage = $currentPage ?: Page::getCurrentPage();
    }

    protected function getActiveTrailPageIDs(): array
    {
        if (!$this->currentPage || $this->currentPage->isError()) {
            return [];
        }

        $parents = $this->navigationService->getTrailToCollection($this->currentPage);

        return array_map(static function (Page $page): int {
            return $page->getCollectionID();
        }, $parents);
    }

    protected function resetState(array $items): void
    {
        foreach ($items as $item) {
            if ($item instanceof PageItem) {
                $item->setIsActive(false);
                $item->setIsActiveParent(false);
                $this->resetState($item->getChildren());
            }
        }
    }

    protected function activateItems(array $items, array $activeTrailPageIDs): void
    {
        if (!$this->currentPage || $this->currentPage->isError()) {
            return;
        }

        $currentPageID = $this->currentPage->getCollectionID();
        foreach ($items as $item) {
            if (!$item instanceof PageItem) {
                continue;
            }

            if ($item->getPageID() === $currentPageID) {
                $item->setIsActive(true);
            } elseif (in_array($item->getPageID(), $activeTrailPageIDs, true)) {
                $item->setIsActiveParent(true);
            }

            $this->activateItems($item->getChildren(), $activeTrailPageIDs);
        }
    }

    public function modify(NavigationInterface $navigation)
    {
        $activeTrailPageIDs = $this->getActiveTrailPageIDs();
        $items = $navigation->getItems();

        $this->resetState($items);
        $this->activateItems($items, $activeTrailPageIDs);
    }
}
