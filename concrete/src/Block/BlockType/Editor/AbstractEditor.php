<?php

namespace Concrete\Core\Block\BlockType\Editor;

abstract class AbstractEditor implements EditorInterface
{
    public function getComponentProps(): array
    {
        return [];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'component' => $this->getComponentKey(),
            'props' => $this->getComponentProps(),
        ];
    }
}
