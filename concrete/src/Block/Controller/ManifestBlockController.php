<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Block\Block;
use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Entity\Block\CollectionVersionBlockData;
use Concrete\Core\Entity\Block\CollectionVersionBlockDataRepository;
use Concrete\Core\Error\ErrorList\ErrorList;
use Doctrine\ORM\EntityManagerInterface;

class ManifestBlockController implements ControllerInterface
{
    public function __construct(
        public BlockManifest $manifest,
        public Serializer $serializer,
        public EntityManagerInterface $entityManager,
        public Block|\Concrete\Core\Entity\Block\BlockType\BlockType|null $obj = null,
    ) {}

    public function ignorePageThemeGridFrameworkContainer(): bool
    {
        return false;
    }

    public function getSets(): array
    {
        return [];
    }

    public function on_start(): void
    {
        // Nothing.
    }

    public function on_before_render(): void
    {
        // Nothing.
    }

    public function runAction($method, $parameters): void
    {
        // Nothing
    }

    public function validate(array $requestArgs): ErrorList
    {
        return new ErrorList();
    }

    public function save(array $requestArgs): void
    {
        $json = $this->serializer->serializeFromRequest($requestArgs);
        if (!$this->obj instanceof Block) {
            return;
        }

        /** @var CollectionVersionBlockDataRepository $repository */
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->obj);
        if ($record === null) {
            $collection = $this->obj->getBlockCollectionObject();
            $version = $collection?->getVersionObject();
            if ($collection === null || $version === null) {
                throw new \RuntimeException('Unable to determine the collection version for this manifest-backed block.');
            }

            $record = new CollectionVersionBlockData();
            $record->setBlockId((int) $this->obj->getBlockID());
            $record->setCollectionId((int) $collection->getCollectionID());
            $record->setCollectionVersionId((int) $version->getVersionID());
        }

        $record->setData($json);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function getBlockTypeDefaultSet(): ?string
    {
        return $this->manifest->getSet();
    }

}
