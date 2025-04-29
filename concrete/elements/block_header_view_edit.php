<?php
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

?>

<concrete-block
    id="b<?=$b->getBlockID()?>"
    name="<?=t($blockType->getBlockTypeName())?>"
>