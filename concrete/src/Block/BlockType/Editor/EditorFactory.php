<?php

namespace Concrete\Core\Block\BlockType\Editor;

use Concrete\Core\Block\BlockType\BlockType as BlockTypeService;
use Concrete\Core\Block\ProvidesEditorInterface;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Support\Facade\Application;

class EditorFactory
{
    public const MODE_ADD = 'add';
    public const MODE_EDIT = 'edit';

    public function createForBlockType(BlockType $blockType, string $mode = self::MODE_EDIT): ?EditorInterface
    {

        // Support for new composable block types:
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

        // Support for v1 block controllers that define their own
        // editors
        if ($blockType->getController() instanceof ProvidesEditorInterface) {
            return $blockType->getController()->getEditor($mode);
        }

        // Note: no support for legacy inline editing. If you need this,
        // you should implement a custom component.

        // Fall back to dialog editing.
        if (!$this->supportsMode($blockType, $mode)) {
            return null;
        }

        return new DialogEditor(
            $mode === self::MODE_ADD
                ? t('Add %s', t($blockType->getBlockTypeName()))
                : t('Edit %s', t($blockType->getBlockTypeName())),
            (string) $blockType->getBlockTypeInterfaceWidth(),
            (string) $blockType->getBlockTypeInterfaceHeight()
        );
    }

    protected function supportsMode(BlockType $blockType, string $mode): bool
    {
        if ($mode === self::MODE_ADD) {
            return $blockType->hasAddTemplate();
        }
        if ($mode === self::MODE_EDIT) {
            return $blockType->hasEditTemplate();
        }

        return false;
    }
}
