<?php

defined('C5_EXECUTE') or die("Access Denied.");

if ($a->isGlobalArea()) {
    $c = Page::getCurrentPage();
    $cID = $c->getCollectionID();
} else {
    $cID = $b->getBlockCollectionID();
    $c = $b->getBlockCollectionObject();
}

$blockStyle = $b->getCustomStyle();
?>

<?php
if (
    $pt->supportsGridFramework()
    && $a->isGridContainerEnabled()
    && !$b->ignorePageThemeGridFrameworkContainer()
) {
    $gf = $pt->getThemeGridFrameworkObject();
    echo '</div>';
    echo $gf->getPageThemeGridFrameworkRowEndHTML();
    echo $gf->getPageThemeGridFrameworkContainerEndHTML();
}

if (is_object($blockStyle)) {
    ?>
    </div>
<?php 
} ?>