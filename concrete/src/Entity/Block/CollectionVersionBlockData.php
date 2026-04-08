<?php

declare(strict_types=1);

namespace Concrete\Core\Entity\Block;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\Concrete\Core\Entity\Block\CollectionVersionBlockDataRepository")
 * @ORM\Table(
 *     name="CollectionVersionBlockData",
 *     indexes={
 *         @ORM\Index(name="bID_cID", columns={"bID", "cID"}),
 *         @ORM\Index(name="cID_cvID", columns={"cID", "cvID"})
 *     }
 * )
 */
class CollectionVersionBlockData
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @var int
     */
    protected $cID;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @var int
     */
    protected $cvID;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @var int
     */
    protected $bID;

    /**
     * @ORM\Column(type="text", options={"default": ""})
     *
     * @var string
     */
    protected $data = '';

    public function getCollectionId(): int
    {
        return (int) $this->cID;
    }

    public function setCollectionId(int $collectionId): void
    {
        $this->cID = $collectionId;
    }

    public function getCollectionVersionId(): int
    {
        return (int) $this->cvID;
    }

    public function setCollectionVersionId(int $collectionVersionId): void
    {
        $this->cvID = $collectionVersionId;
    }

    public function getBlockId(): int
    {
        return (int) $this->bID;
    }

    public function setBlockId(int $blockId): void
    {
        $this->bID = $blockId;
    }

    public function getData(): string
    {
        return (string) $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }
}
