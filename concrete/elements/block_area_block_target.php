<?php
defined('C5_EXECUTE') or die('Access Denied.');

$resolvedAfterBlockId = isset($afterBlockId) ? (int) $afterBlockId : 0;
$resolvedTargetIndex = isset($targetIndex) ? (int) $targetIndex : 0;
$container = null;
$page = $a->getAreaCollectionObject();
$pt = $page ? $page->getCollectionThemeObject() : null;
if ($pt && $pt->supportsGridFramework() && $a->isGridContainerEnabled()) {
    $gf = $pt->getThemeGridFrameworkObject();
    $container = [
        'start' => $gf->getPageThemeGridFrameworkContainerStartHTML()
            . $gf->getPageThemeGridFrameworkRowStartHTML()
            . sprintf(
                '<div class="%s">',
                $gf->getPageThemeGridFrameworkColumnClassesForSpan(
                    min($a->getAreaGridMaximumColumns(), $gf->getPageThemeGridFrameworkNumColumns())
                )
            ),
        'end' => '</div>' . $gf->getPageThemeGridFrameworkRowEndHTML() . $gf->getPageThemeGridFrameworkContainerEndHTML(),
    ];
}
?>

<concrete-area-block-target
    area-id="<?=$a->getAreaID()?>"
    page-id="<?=$a->getAreaCollectionObject()->getCollectionID()?>"
    area-handle="<?=h($a->getAreaHandle())?>"
    after-block-id="<?=$resolvedAfterBlockId?>"
    target-index="<?=$resolvedTargetIndex?>"
    container='<?=h(json_encode($container))?>'
></concrete-area-block-target>
