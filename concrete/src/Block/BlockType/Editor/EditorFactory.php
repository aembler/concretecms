<?php

namespace Concrete\Core\Block\BlockType\Editor;

use Concrete\Core\Block\BlockType\BlockType as BlockTypeService;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Support\Facade\Application;

class EditorFactory
{
    public const MODE_ADD = 'add';
    public const MODE_EDIT = 'edit';

    public function createForBlockType(BlockType $blockType, string $mode = self::MODE_EDIT): ?EditorInterface
    {
        if (!$this->supportsMode($blockType, $mode)) {
            return null;
        }

        $controller = $blockType->getController();
        if ($this->supportsInlineEditor($controller, $mode)) {
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

        return new DialogEditor(
            t('Edit %s', t($blockType->getBlockTypeName())),
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

    protected function supportsInlineEditor($controller, string $mode): bool
    {
        if (!is_object($controller)) {
            return false;
        }

        if ($mode === self::MODE_ADD) {
            return method_exists($controller, 'supportsInlineAdd')
                && $controller->supportsInlineAdd();
        }

        if ($mode === self::MODE_EDIT) {
            return method_exists($controller, 'supportsInlineEdit')
                && $controller->supportsInlineEdit();
        }

        return false;
    }
}
