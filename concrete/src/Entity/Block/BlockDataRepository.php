<?php

declare(strict_types=1);

namespace Concrete\Core\Entity\Block;

use Concrete\Core\Block\Block;

class BlockDataRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByBlock(int|Block $block): ?BlockData
    {
        if ($block instanceof Block) {
            $blockID = $block->getBlockID();
        } else {
            $blockID = $block;
        }
        return $this->findOneBy(['bID' => $blockID]);
    }
}
