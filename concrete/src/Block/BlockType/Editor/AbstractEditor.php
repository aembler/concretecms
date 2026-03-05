<?php

namespace Concrete\Core\Block\BlockType\Editor;

use Concrete\Core\Application\UserInterface\Component\AbstractComponent;

abstract class AbstractEditor extends AbstractComponent implements EditorInterface
{
    public function getComponentProps(): array
    {
        return [];
    }
}
