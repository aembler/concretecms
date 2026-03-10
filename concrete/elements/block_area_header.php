<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>
<concrete-area
    total-blocks="<?=count($blocks)?>"
    name="<?=t('Area: %s', $a->getAreaDisplayName())?>"
    page-id="<?=$a->getAreaCollectionObject()->getCollectionID()?>"
    area-id="<?=$a->getAreaID()?>"
    area-handle="<?=h($a->getAreaHandle())?>"
    data-area-id="<?=$a->getAreaID()?>"
    data-area-handle="<?=h($a->getAreaHandle())?>"
>
<?php
View::element('block_area_block_target', ['a' => $a, 'afterBlockId' => 0, 'targetIndex' => 0]);
?>
