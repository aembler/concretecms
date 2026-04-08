<?php

declare(strict_types=1);

namespace Concrete\Core\Entity\Block;

use Concrete\Core\Block\Block;
use Concrete\Core\Page\Collection\Collection;
use Concrete\Core\Page\Collection\Version\Version;

class CollectionVersionBlockDataRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByBlock($block, $collection = null, $version = null): ?CollectionVersionBlockData
    {
        if ($block instanceof Block) {
            $collection = $collection ?? $block->getBlockCollectionObject();
            $version = $version ?? $block->getBlockCollectionObject()?->getVersionObject();
            $block = $block->getBlockID();
        }

        if ($collection instanceof Collection) {
            $collection = $collection->getCollectionID();
        }

        if ($version instanceof Version) {
            $version = $version->getVersionID();
        }

        if (!$block || !$collection || !$version) {
            return null;
        }

        return $this->findOneBy([
            'bID' => (int) $block,
            'cID' => (int) $collection,
            'cvID' => (int) $version,
        ]);
    }

    /**
     * @return array<int, \Concrete\Core\Entity\Block\CollectionVersionBlockData>
     */
    public function findByCollection($collection, $version = null): array
    {
        if ($collection instanceof Collection) {
            $collection = $collection->getCollectionID();
        }

        $criteria = [
            'cID' => (int) $collection,
        ];

        if ($version instanceof Version) {
            $version = $version->getVersionID();
        }

        if ($version !== null) {
            $criteria['cvID'] = (int) $version;
        }

        return $this->findBy($criteria);
    }
}
