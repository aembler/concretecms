<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Block\Manifest\BlockManifestParser;
use Concrete\Core\Block\Manifest\Locator;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Block\BlockType\BlockType as BlockTypeService;
use Concrete\Core\Filesystem\FileLocator;

class ControllerFactory implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function __construct(
        private BlockManifestParser $manifestParser,
    ) {}
    public function createFromBlockType(BlockType $bt): BlockController|ControllerInterface
    {
        // Classic individual block type controller support
        $controller = BlockTypeService::getBlockTypeMappedClass(
            $bt->getBlockTypeHandle(), $bt->getPackageHandle(), $bt->getBlockTypeActiveVersion()
        );
        if ($controller) {
            return $this->app->make($controller, ['obj' => $bt]);
        }

        return $this->getManifestBlockController($bt);
    }

    private function getManifestBlockController(BlockType|Block $object): ManifestBlockController
    {
        $locator = $this->app->make(Locator::class);
        $record = $locator->getRecord($object);
        if ($record->exists()) {
            return $this->app->make(ManifestBlockController::class, ['object' => $object]);
        } else {
            throw new \Exception(t('Unable to locate manifest.'));
        }
    }

    public function createFromBlock(Block $block): BlockController|ControllerInterface
    {
        // Classic individual block type controller support
        $controller = BlockTypeService::getBlockTypeMappedClass(
            $block->getBlockTypeHandle(), $block->getPackageHandle(), $block->getBlockVersion()
        );
        if ($controller) {
            $instance = $this->app->make($controller, ['obj' => $block]);
            // I don't know why we need this.
            $instance->setBlockObject($block);
            $instance->setAreaObject($block->getBlockAreaObject());
            return $instance;
        }
        return $this->getManifestBlockController($block);
    }
}
