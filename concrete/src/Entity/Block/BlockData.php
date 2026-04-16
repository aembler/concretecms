<?php

declare(strict_types=1);

namespace Concrete\Core\Entity\Block;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Block\Manifest\Value\StorageValue;

/**
 * @ORM\Entity(repositoryClass="\Concrete\Core\Entity\Block\BlockDataRepository")
 * @ORM\Table(
 *     name="BlockDataObjects"
 * )
 */
class BlockData
{

    public function __construct(
        /**
         * @ORM\Id
         * @ORM\Column(type="integer", options={"unsigned": true})
         *
         * @var int
         */
        public int $bID,

        /**
         * @ORM\Column(type="json")
         */
        public StorageValue|array $data,
    ) {}

}
