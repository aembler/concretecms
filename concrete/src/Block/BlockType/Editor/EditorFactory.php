<?php

namespace Concrete\Core\Block\BlockType\Editor;

use Concrete\Core\Block\BlockType\BlockType as BlockTypeService;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Support\Facade\Application;

class EditorFactory
{
    public function createFromBlockType(BlockType $blockType): EditorInterface
    {
        $controller = $blockType->getController();
        if (
            is_object($controller)
            && method_exists($controller, 'supportsInlineEdit')
            && $controller->supportsInlineEdit()
        ) {
            return new InlineEditor();
        }

        /** @var FileLocator $locator */
        $locator = Application::getFacadeApplication()->make(FileLocator::class);
        $packageHandle = (string) $blockType->getPackageHandle();
        if ($packageHandle !== '') {
            $locator->addPackageLocation($packageHandle);
        }

        $record = $locator->getRecord(
            BlockTypeService::getBlockTypeRelativePath(
                $blockType->getBlockTypeHandle(),
                'manifest.xml',
                $packageHandle,
                $blockType->getBlockTypeActiveVersion()
            )
        );
        if ($record && $record->exists()) {
            return new ComposableEditor();
        }

        return new DialogEditor();
    }
}
