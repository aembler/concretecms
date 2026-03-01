<?php
defined('C5_EXECUTE') or die('Access Denied.');

$resolvedAfterBlockId = isset($afterBlockId) ? (int) $afterBlockId : 0;
$resolvedTargetIndex = isset($targetIndex) ? (int) $targetIndex : 0;
?>

<concrete-area-block-target
    area-id="<?=$a->getAreaID()?>"
    page-id="<?=$a->getAreaCollectionObject()->getCollectionID()?>"
    area-handle="<?=h($a->getAreaHandle())?>"
    after-block-id="<?=$resolvedAfterBlockId?>"
    target-index="<?=$resolvedTargetIndex?>"
></concrete-area-block-target>
