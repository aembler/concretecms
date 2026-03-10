<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>
<concrete-area
    total-blocks="<?=count($blocks)?>"
    name="<?=t('%s Area', $a->getAreaDisplayName())?>"
    page-id="<?=$a->getAreaCollectionObject()->getCollectionID()?>"
    area-handle="<?=h($a->getAreaHandle())?>"
>
<?php
View::element('block_area_block_target', ['a' => $a, 'afterBlockId' => 0, 'targetIndex' => 0]);
?>
