<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Block\Manifest\Locator;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Entity\Block\CollectionVersionBlockData;
use Concrete\Core\Entity\Block\CollectionVersionBlockDataRepository;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Logging\LoggerAwareInterface;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Controller\Backend\UserInterface\Block as BackendBlockController;

class ManifestBlockController extends AbstractController implements ControllerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var string|null
     */
    protected $action;

    /**
     * @var array<mixed>|null
     */
    protected $parameters;

    public function getLoggerChannel()
    {
        return Channels::CHANNEL_CONTENT;
    }

    public function __construct(
        public Locator $locator,
        public BlockManifestParser $manifestParser,
        public Serializer $serializer,
        public EntityManagerInterface $entityManager,
        public Block|BlockTypeEntity|null $object = null,
    ) {
        parent::__construct();
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

    private function getManifest(): BlockManifest
    {
        $manifest = $this->manifestParser->parseFile($record->file);

    }
    public function duplicate(int $newBlockId): void
    {
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->object);
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

    public function delete(): void
    {
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->object);
        if ($record) {
            $this->entityManager->remove($record);
            $this->entityManager->flush();
        }
    }

    public function view()
    {
        $payload = $this->loadStoredPayload();
        $fields = [];

        $record = $this->locator->getRecord($this->object);
        $manifest = $this->manifestParser->parseFile($record->file);

        foreach ($manifest->getFields() as $field) {
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
                        'blockType' => $manifest->getHandle(),
                    ]
                );
                continue;
            }

            $storedValue = $fieldType->extractValueFromStorage($payload, $field);
            $viewValue = $fieldType->createViewValue($storedValue, $field);

            $fields[$field->getId()] = $viewValue;
        }

        $this->set('manifest', $manifest);
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
        if (!$this->object instanceof Block) {
            return Serializer::emptyEnvelope();
        }

        /** @var CollectionVersionBlockDataRepository $repository */
        $repository = $this->entityManager->getRepository(CollectionVersionBlockData::class);
        $record = $repository->findOneByBlock($this->object);
        if ($record === null || $record->getData() === '') {
            return Serializer::emptyEnvelope();
        }

        try {
            $payload = json_decode($record->getData(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->logger->notice(
                'Unable to decode persisted manifest data for block "{blockId}". Falling back to an empty envelope.',
                [
                    'blockId' => $this->object->getBlockID(),
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

    public function add(): JsonResponse
    {
        $blockType = BlockType::getByID($this->request->query->get('btID'));
        $controller = $blockType->getController();
        if (!($controller instanceof ManifestBlockController)) {
            throw new \UserMessageException(t('This block type does not use a manifest.'));
        }
        $record = $this->locator->getRecord($blockType);
        $manifest = $this->manifestParser->parseFile($record->file);
        return new JsonResponse($manifest);
    }
    public function edit(): JsonResponse
    {
        $bID = $this->request->query->get('bID');
        $arHandle = $this->request->get('arHandle');
        $page = BackendBlockController::getPage($this->request->get('cID'));
        $a = Area::get($page, $arHandle);
        if (!is_object($a)) {
            throw new UserMessageException('Invalid Area');
        }
        if (!$a->isGlobalArea()) {
            $this->set('isGlobalArea', false);
            $b = Block::getByID($bID, $page, $a);
        } else {
            $stack = Stack::getByName($arHandle);
            $sc = Page::getByID($stack->getCollectionID());
            $b = Block::getByID($bID, $sc, STACKS_AREA_NAME);
            if ($b) {
                $b->setBlockAreaObject($a); // set the original area object
            }
        }
        if (!$b) {
            throw new UserMessageException(t('Access Denied'));
        }
        $record = $this->locator->getRecord($b);
        $manifest = $this->manifestParser->parseFile($record->file);
        return new JsonResponse($manifest);
    }

}
