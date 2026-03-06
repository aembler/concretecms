<?php
use Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer;
use Concrete\Core\Block\BlockType\Editor\EditorFactory;

defined('C5_EXECUTE') or die("Access Denied.");
$blockType = $b->getBlockTypeObject();
if ($a->isGlobalArea()) {
    $c = Page::getCurrentPage();
    $cID = $c->getCollectionID();
} else {
    $cID = $b->getBlockCollectionID();
    $c = $b->getBlockCollectionObject();
}

$pt = $c->getCollectionThemeObject();

View::element('block_header_view', ['a' => $a, 'b' => $b, 'c' => $c, 'pt' => $pt]);

if ($blockType->getBlockTypeHandle() === BLOCK_HANDLE_CONTAINER_PROXY) { ?>

<concrete-container>

<?php } else {

    $blockTypeData = app(BlockTypeTransformer::class)->transform($blockType);
    $editor = app(EditorFactory::class)->createForBlock($b, EditorFactory::MODE_EDIT);
    $isMasterCollection = $c->isMasterCollection();
    ?>

<concrete-block
    id="b<?=$b->getBlockID()?>"
    block-id="<?=$b->getBlockID()?>"
    page-id="<?=$c->getCollectionID()?>"
    name="<?=t($blockType->getBlockTypeName())?>"
    blocktype='<?=h(json_encode($blockTypeData))?>'
    editor='<?=h(json_encode($editor))?>'
    selected-variant="<?=$b->getBlockFilename()?>"
    variants='<?=json_encode($blockType->getBlockTypeCustomTemplates($b))?>'
    area-handle="<?=h($a->getAreaHandle())?>"
    is-master-collection="<?=$isMasterCollection ? '1' : '0'?>"
>

<?php } ?>
