<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Controller\Backend\UserInterface\Block as BackendBlockController;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Locator;
use Concrete\Core\Block\Manifest\Value\ValueFactory;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Entity\Block\BlockData;
use Concrete\Core\Entity\Block\BlockDataRepository;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Logging\LoggerAwareInterface;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        public ValueFactory $valueFactory,
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
        $record = $this->locator->getRecord($this->object);
        return $this->manifestParser->parseFile($record->file);
    }

    private function getEmptyEditorData(BlockManifest $manifest): array
    {
        return [
            'meta' => [],
            'values' => [],
            'schemaVersion' => $manifest->getSchemaVersion(),
        ];
    }
    public function duplicate(int $newBlockId): void
    {
        $repository = $this->entityManager->getRepository(BlockData::class);
        $record = $repository->findOneByBlock($this->object);
        if ($record) {
            /**
             * @var $newRecord BlockData
             */
            $newRecord = clone $record;
            $newRecord->bID = $newBlockId;
            $this->entityManager->persist($newRecord);
            $this->entityManager->flush();
        }
    }

    public function delete(): void
    {
        $repository = $this->entityManager->getRepository(BlockData::class);
        $record = $repository->findOneByBlock($this->object);
        if ($record) {
            $this->entityManager->remove($record);
            $this->entityManager->flush();
        }
    }

    public function view()
    {
        $repository = $this->entityManager->getRepository(BlockData::class);
        $record = $repository->findOneByBlock($this->object);
        $manifest = $this->getManifest();
        if ($record instanceof BlockData) {
            $value = $this->valueFactory->createViewValue($manifest, (array) $record->data['values']);
        }

        $this->set('manifest', $manifest);
        $this->set('values', $value->values);
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
        $manifest = $this->getManifest();
        $value = $this->valueFactory->createStorageValueFromArray($manifest, $requestArgs);

        /** @var BlockDataRepository $repository */
        $repository = $this->entityManager->getRepository(BlockData::class);
        $record = $repository->findOneByBlock($this->object);
        if ($record === null) {
            $record = new BlockData($this->object->getBlockID(), $value);
        } else {
            $record->data = $value;
        }
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function getBlockTypeDefaultSet(): ?string
    {
        return $this->getManifest()->getSet();
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
        return new JsonResponse([
            'manifest' => $manifest,
            'data' => $this->getEmptyEditorData($manifest),
        ]);
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

        $this->object = $b;
        $repository = $this->entityManager->getRepository(BlockData::class);
        $record = $repository->findOneByBlock($this->object);
        $manifest = $this->getManifest();
        if ($record instanceof BlockData) {
            return new JsonResponse([
                'data' => $record->data,
                'manifest' => $manifest,
            ]);
        }

        return new JsonResponse([
            'manifest' => $manifest,
            'data' => $this->getEmptyEditorData($manifest),
        ]);
    }

}
