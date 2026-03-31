<?php

namespace Concrete\Core\Navigation\Item;

use Concrete\Core\Application\UserInterface\Icon\FontAwesomeIcon;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\InlineSvgIcon;

class DashboardPageItem extends PageItem
{
    protected function getDashboardIconValue(): ?string
    {
        $icon = $this->page->getAttribute('icon_dashboard');
        if (is_string($icon) && trim($icon) !== '') {
            return trim($icon);
        }

        return null;
    }

    protected function createIconFromValue(string $icon): ?IconInterface
    {
        if (preg_match('/^<svg\\b/i', ltrim($icon)) === 1) {
            return new InlineSvgIcon($icon);
        }

        return new FontAwesomeIcon($icon);
    }

    public function getIcon(): ?IconInterface
    {
        $icon = $this->getDashboardIconValue();
        if ($icon === null) {
            return null;
        }

        return $this->createIconFromValue($icon);
    }

    public function getKeywords(): ?string
    {
        return $this->page->getAttribute('meta_keywords');
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['keywords'] = $this->getKeywords();
        $data['children'] = $this->getChildren();
        $data['icon'] = $this->getIcon();

        return $data;
    }
}
