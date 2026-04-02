<?php
namespace Concrete\Controller\SinglePage\Dashboard;

use Concrete\Core\Application\UserInterface\Icon\FontAwesomeIcon;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\InlineSvgIcon;
use Concrete\Core\Application\UserInterface\Dashboard\Navigation\FullNavigationFactory;
use Concrete\Core\Navigation\Item\LinkItemInterface;
use Concrete\Core\Navigation\Item\SupportsChildrenItemInterface;
use Concrete\Core\Navigation\Modifier\NavigationStartingPointModifier;
use Concrete\Core\Navigation\NavigationModifier;
use Concrete\Core\Page\Controller\DashboardPageController;
use Page;

class System extends DashboardPageController
{
    public $helpers = array('form');

    protected function getCategoryItems(): array
    {
        $system = Page::getByPath('/dashboard/system');
        $navigation = app(FullNavigationFactory::class)->createNavigation();
        $modifier = app(NavigationModifier::class);
        $modifier->addModifier(new NavigationStartingPointModifier($system));
        $navigation = $modifier->process($navigation);

        return $navigation->getItems();
    }

    protected function getItemData(LinkItemInterface $item): array
    {
        $description = '';
        $icon = null;
        if (method_exists($item, 'getPageID')) {
            $page = Page::getByID($item->getPageID(), 'ACTIVE');
            if ($page && !$page->isError()) {
                $description = $this->getPageDescription($page);
                $icon = $this->getPageIconData($page);
            }
        }

        return [
            'name' => $item->getName(),
            'url' => $item->getUrl(),
            'description' => $description,
            'icon' => $icon,
        ];
    }

    protected function getPageDescription(Page $page): string
    {
        $description = trim((string) $page->getCollectionDescription());
        if ($description !== '') {
            return $description;
        }

        $metaDescription = $page->getAttribute('meta_description');
        if (is_string($metaDescription)) {
            return trim($metaDescription);
        }

        return '';
    }

    protected function getDashboardIconValue(Page $page): ?string
    {
        foreach (['icon_dashboard', 'dashboard_icon'] as $attributeHandle) {
            $icon = $page->getAttribute($attributeHandle);
            if (is_string($icon) && trim($icon) !== '') {
                return trim($icon);
            }
        }

        return null;
    }

    protected function createIconFromValue(string $icon): IconInterface
    {
        if (preg_match('/^<svg\\b/i', ltrim($icon)) === 1) {
            return new InlineSvgIcon($icon);
        }

        return new FontAwesomeIcon($icon);
    }

    protected function getPageIconData(Page $page): ?array
    {
        $icon = $this->getDashboardIconValue($page);
        if ($icon === null) {
            return null;
        }

        return $this->createIconFromValue($icon)->jsonSerialize();
    }

    public function view()
    {
        $this->enableNativeMobile();
        $categories = array();

        foreach ($this->getCategoryItems() as $categoryItem) {
            if (!($categoryItem instanceof LinkItemInterface)) {
                continue;
            }

            $pages = [];
            if ($categoryItem instanceof SupportsChildrenItemInterface && count($categoryItem->getChildren())) {
                foreach ($categoryItem->getChildren() as $childItem) {
                    if ($childItem instanceof LinkItemInterface) {
                        $pages[] = $this->getItemData($childItem);
                    }
                }
            } else {
                $pages[] = $this->getItemData($categoryItem);
            }

            $categories[] = [
                'name' => $categoryItem->getName(),
                'pages' => $pages,
            ];
        }

        $this->set('categories', $categories);
    }
}
