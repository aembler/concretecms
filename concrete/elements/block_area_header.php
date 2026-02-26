<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>
<concrete-area
    total-blocks="<?=count($blocks)?>"
    name="<?=$a->getAreaDisplayName()?>"
    area-id="<?=$a->getAreaID()?>"
    area-handle="<?=h($a->getAreaHandle())?>"
    data-area-id="<?=$a->getAreaID()?>"
    data-area-handle="<?=h($a->getAreaHandle())?>"
>
