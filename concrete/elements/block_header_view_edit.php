<?php
use Concrete\Controller\Dialog\Block\Delete as DeleteBlockDialogController;
use Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer;
use Concrete\Core\Support\Facade\Url;

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
    $isMasterCollection = $c->isMasterCollection();
    $defaultsMessage = '';
    if ($isMasterCollection) {
        if ($blockType->getBlockTypeHandle() == BLOCK_HANDLE_LAYOUT_PROXY) {
            $defaultsMessage = t('Warning! This layout is contained in the page type defaults. Anywhere this layout is used may have content deleted. This cannot be undone.');
        } else {
            $defaultsMessage = t('Warning! This block is contained in the page type defaults. Any blocks aliased from this block in the site will be deleted. This cannot be undone.');
        }
    }
    if ($blockType->getBlockTypeHandle() == BLOCK_HANDLE_LAYOUT_PROXY) {
        $deleteMessage = t('Are you sure you wish to delete this layout? It will remove the blocks that are contained within it.');
    } else {
        $deleteMessage = t('Are you sure you wish to delete this %s block?', $blockType->getBlockTypeName());
    }

    $token = app('token')->generate(DeleteBlockDialogController::class);
    $query = '&cID=' . $c->getCollectionID()
        . '&bID=' . $b->getBlockID()
        . '&arHandle=' . urlencode($a->getAreaHandle())
        . '&ccm_token=' . urlencode($token);
    $editQuery = '&cID=' . $c->getCollectionID()
        . '&bID=' . $b->getBlockID()
        . '&arHandle=' . urlencode($a->getAreaHandle());
    $deleteAction = (string) Url::to('/ccm/system/dialogs/block/delete/submit') . '?' . ltrim($query, '&');
    $deleteAllAction = (string) Url::to('/ccm/system/dialogs/block/delete/submit_all') . '?' . ltrim($query, '&');
    $editAction = (string) Url::to('/ccm/system/dialogs/block/edit') . '?' . ltrim($editQuery, '&');

    ?>

<concrete-block
    id="b<?=$b->getBlockID()?>"
    block-id="<?=$b->getBlockID()?>"
    page-id="<?=$c->getCollectionID()?>"
    name="<?=t($blockType->getBlockTypeName())?>"
    blocktype='<?=h(json_encode($blockTypeData))?>'
    selected-variant="<?=$b->getBlockFilename()?>"
    variants='<?=json_encode($blockType->getBlockTypeCustomTemplates($b))?>'
    edit-action="<?=h($editAction)?>"
    edit-dialog-title="<?=h(t('Edit %s', t($blockType->getBlockTypeName())))?>"
    edit-dialog-width="<?=h((string) $blockType->getBlockTypeInterfaceWidth())?>"
    edit-dialog-height="<?=h((string) $blockType->getBlockTypeInterfaceHeight())?>"
    delete-action="<?=h($deleteAction)?>"
    delete-all-action="<?=h($deleteAllAction)?>"
    delete-message="<?=h($deleteMessage)?>"
    delete-defaults-message="<?=h($defaultsMessage)?>"
    area-handle="<?=h($a->getAreaHandle())?>"
    delete-is-master-collection="<?=$isMasterCollection ? '1' : '0'?>"
    delete-dialog-title="<?=h(t('Delete'))?>"
    delete-progressive-operation-title="<?=h(t('Delete Blocks'))?>"
>

<?php } ?>
