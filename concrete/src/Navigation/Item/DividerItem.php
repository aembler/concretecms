<?php

namespace Concrete\Core\Navigation\Item;

use HtmlObject\Element;
use HtmlObject\Traits\Tag;

class DividerItem implements RenderableItemInterface, SerializableItemInterface
{

    public function render(): Tag
    {
        return new Element('hr');
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'type' => 'divider',
        ];
    }
}
