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

<concrete-container
    :container-block-id="<?=$b->getBlockID()?>"
    >


<?php } else {

    $blockTypeData = app(BlockTypeTransformer::class)->transform($blockType);
    $editor = app(EditorFactory::class)->createForBlock($b, EditorFactory::MODE_EDIT);
    $isMasterCollection = $c->isMasterCollection();
    $lang = [
        'delete' => [
            'dialogTitle' => t('Delete Block'),
            'dialogMessage' => t('Are you sure you want to remove this block?'),
            'defaultsDialogMessage' => t('Warning! This block is contained in the page type defaults. Any blocks aliased from this block in the site will be deleted. This cannot be undone.'),
        ],
    ];
    ?>

<concrete-block
    id="b<?=$b->getBlockID()?>"
    block-id="<?=$b->getBlockID()?>"
    page-id="<?=$c->getCollectionID()?>"
    name="<?=t($blockType->getBlockTypeName())?>"
    blocktype='<?=h(json_encode($blockTypeData))?>'
    editor='<?=h(json_encode($editor))?>'
    lang='<?=h(json_encode($lang))?>'
    selected-variant="<?=$b->getBlockFilename()?>"
    variants='<?=json_encode($blockType->getBlockTypeCustomTemplates($b))?>'
    area-handle="<?=h($a->getAreaHandle())?>"
    delete-token="<?=app('token')->generate('delete_block')?>"
    is-master-collection="<?=$isMasterCollection ? '1' : '0'?>"
>

<?php } ?>
