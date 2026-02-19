<?php

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Block\BlockType\Editor\EditorFactory;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use League\Fractal\TransformerAbstract;

class BlockTypeTransformer extends TransformerAbstract
{

    public function __construct(
        private EditorFactory $editorFactory,
    ) {}

    public function transform(BlockType $blockType): array
    {
        return [
            'id' => $blockType->getBlockTypeID(),
            'handle' => $blockType->getBlockTypeHandle(),
            'name' => $blockType->getBlockTypeName(),
            'description' => $blockType->getBlockTypeDescription(),
            'editor' => $this->editorFactory->createFromBlockType($blockType),
        ];
    }


}
