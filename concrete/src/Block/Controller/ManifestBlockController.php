<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Block\Block;
use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Entity\Block\CollectionVersionBlockData;
use Concrete\Core\Entity\Block\CollectionVersionBlockDataRepository;
use Concrete\Core\Error\ErrorList\ErrorList;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Psr\Log\LoggerInterface;

class ManifestBlockController extends AbstractController implements ControllerInterface
{
    /**
     * @var string|null
     */
    protected $action;

    /**
     * @var array<mixed>|null
     */
    protected $parameters;

    public function __construct(
        public BlockManifest $manifest,
        public Serializer $serializer,
        public EntityManagerInterface $entityManager,
        public LoggerInterface $logger,
        public Block|\Concrete\Core\Entity\Block\BlockType\BlockType|null $obj = null,
    ) {
    }

    /**
     * The values to be sent to views.
     *
     * @var array
     */
    protected $sets = [];

    public function ignorePageThemeGridFrameworkContainer(): bool
    {
        return false;
    }

    public function duplicate(int $newBlockId): void
    {
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->obj);
        if ($record) {
            /**
             * @var $newRecord CollectionVersionBlockData
             */
            $newRecord = clone $record;
            $newRecord->setBlockId($newBlockId);
            $this->entityManager->persist($newRecord);
            $this->entityManager->flush();
        }
    }

    public function view()
    {
        $payload = $this->loadStoredPayload();
        $fields = [];

        foreach ($this->manifest->getFields() as $field) {
            if (!$field instanceof FieldDefinition) {
                continue;
            }

            $fieldType = $field->getFieldType();
            if ($fieldType === null) {
                $this->logger->notice(
                    'Skipping manifest field "{fieldId}" during view hydration because its type "{fieldType}" is not registered.',
                    [
                        'fieldId' => $field->getId(),
                        'fieldType' => $field->getType(),
                        'blockType' => $this->manifest->getHandle(),
                    ]
                );
                continue;
            }

            $storedValue = $fieldType->extractValueFromStorage($payload, $field);
            $viewValue = $fieldType->createViewValue($storedValue, $field);

            $fields[$field->getId()] = $viewValue;
        }

        $this->set('manifest', $this->manifest);
        $this->set('manifestData', $payload);
        $this->set('fields', $fields);
    }

    public function validate(array $requestArgs): ErrorList
    {
        return new ErrorList();
    }

    public function runAction($action, $parameters = [])
    {
        $this->action = $action;
        $this->parameters = $parameters;
        if (is_callable([$this, $action])) {
            return call_user_func_array([$this, $action], $parameters);
        }
    }

    public function save(array $requestArgs): void
    {
        $json = $this->serializer->serializeFromRequest($this->manifest, $requestArgs);

        /** @var CollectionVersionBlockDataRepository $repository */
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->obj);
        if ($record === null) {
            $page = $this->obj->getBlockCollectionObject();
            $version = $page->getVersionObject();
            $record = new CollectionVersionBlockData();
            $record->setBlockId((int) $this->obj->getBlockID());
            $record->setCollectionId((int) $page->getCollectionID());
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

    /**
     * @return array<string, mixed>
     */
    protected function loadStoredPayload(): array
    {
        if (!$this->obj instanceof Block) {
            return Serializer::emptyEnvelope();
        }

        /** @var CollectionVersionBlockDataRepository $repository */
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->obj);
        if ($record === null || $record->getData() === '') {
            return Serializer::emptyEnvelope();
        }

        try {
            $payload = json_decode($record->getData(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->logger->notice(
                'Unable to decode persisted manifest data for block "{blockId}". Falling back to an empty envelope.',
                [
                    'blockId' => $this->obj->getBlockID(),
                    'blockType' => $this->manifest->getHandle(),
                    'exception' => $e,
                ]
            );

            return Serializer::emptyEnvelope();
        }

        if (!is_array($payload)) {
            return Serializer::emptyEnvelope();
        }

        return array_replace_recursive(Serializer::emptyEnvelope(), $payload);
    }

}
