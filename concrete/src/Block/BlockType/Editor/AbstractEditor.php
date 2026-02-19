<?php

namespace Concrete\Core\Block\BlockType\Editor;

abstract class AbstractEditor implements EditorInterface
{

    public function jsonSerialize(): mixed
    {
        return [
            'component' => $this->getComponent(),
        ];
    }
}