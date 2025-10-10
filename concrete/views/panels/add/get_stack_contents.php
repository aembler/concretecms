<?php

defined('C5_EXECUTE') or die('Access Denied.');

$blockPreviewUrl = URL::to('/ccm/system/block/preview');
?>
<div class="blocks">
    <?php
    foreach ($blocks as $block) {
        $type = $block->getBlockTypeObject();
        $icon = $ci->getBlockTypeIconURL($type);
        if ($block->getAreaHandle() !== STACKS_AREA_NAME) {
            // This block is not in the main area of the stack, we can't add it.
            // The CollectionVersionBlocks table includes all blocks even those
            // that might be contained within area layouts or containers. We
            // Don't want to show those individually so let's skip those that
            // don't have the area handle set to the main area (which is how we
            // know that they're nested in a layout or container.)
            continue;
        }
        ?>
        <div
            class="block ccm-panel-add-block-draggable-block-type"
            data-panel-add-block-drag-item="block"
            data-cID="<?= $stack->getCollectionID() ?>"
            data-block-type-handle="<?= $type->getBlockTypeHandle() ?>"
            data-dialog-title="<?= t('Add %s', t($type->getBlockTypeName())) ?>"
            data-dialog-width="<?= $type->getBlockTypeInterfaceWidth() ?>"
            data-dialog-height="<?= $type->getBlockTypeInterfaceHeight() ?>"
            data-has-add-template="<?= $type->hasAddTemplate() ?>"
            data-supports-inline-add="<?= $type->supportsInlineAdd() ?>"
            data-btID="<?= $type->getBlockTypeID() ?>"
            data-dragging-avatar="<?= h('<div class="ccm-block-icon-wrapper d-flex align-items-center justify-content-center"><img src="' . $icon . '" /></div><p><span>' . t($type->getBlockTypeInSetName()) . '</span></p>') ?>"
            title="<?= t($type->getBlockTypeName()) ?>"
            data-block-id="<?= (int) $block->getBlockID() ?>"
        >
            <div class="block-name">
                <span class="handle"><?= h($type->getBlockTypeName()) ?></span>
            </div>
            <div class="block-content">
                <iframe src="<?= $blockPreviewUrl->setQuery(['bID' => $block->getBlockID(), 'sID' => $stack->getCollectionID(), 'cID' => $c->getCollectionID()]); ?>" scrolling="no"></iframe>
            </div>
            <div class="block-handle"></div>
        </div>
        <?php
    }
    ?>
</div>
