<?php

namespace Concrete\Core\Block\BlockType\Editor;

use Concrete\Core\Entity\Block\BlockType\BlockType;

class EditorFactory
{
    public function createFromBlockType(BlockType $blockType): EditorInterface
    {
        // @todo - make this work by checking for manifest.xml, etc...
        return new ComposableEditor();
    }
}