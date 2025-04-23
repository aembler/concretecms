<?php
defined('C5_EXECUTE') or die("Access Denied.");
$app = \Core::make('app');

use Concrete\Core\Permission\Key\Key;

if ($a->isGlobalArea()) {
    $c = Page::getCurrentPage();
    $cID = $c->getCollectionID();
} else {
    $cID = $b->getBlockCollectionID();
    $c = $b->getBlockCollectionObject();
}

$p = new Permissions($b);
$css = $b->getCustomStyle();
$pt = $c->getCollectionThemeObject();

?>
<?php if (is_object($css) && $b->getBlockTypeHandle() == BLOCK_HANDLE_LAYOUT_PROXY) {
    ?>
    <?php // in this instance, the css container comes OUTSIDE any theme container ?>
    <div class="<?php echo $css->getContainerClass(); ?>"
    <?php if ($css->getCustomStyleID()) { ?>
    id="<?php echo $css->getCustomStyleID(); ?>"
    <?php } ?>
    <?php if ($css->getCustomStyleElementAttribute()) { ?>
    <?php echo $css->getCustomStyleElementAttribute(); ?>
    <?php } ?>
    >
<?php
} ?>

<?php
if (
    $pt->supportsGridFramework()
    && $a->isGridContainerEnabled()
    && !$b->ignorePageThemeGridFrameworkContainer()
) {
    $gf = $pt->getThemeGridFrameworkObject();
    echo $gf->getPageThemeGridFrameworkContainerStartHTML();
    echo $gf->getPageThemeGridFrameworkRowStartHTML();
    printf('<div class="%s">', $gf->getPageThemeGridFrameworkColumnClassesForSpan(
        min($a->getAreaGridMaximumColumns(), $gf->getPageThemeGridFrameworkNumColumns())
    ));
}
?>
    <?php if (is_object($css) && $b->getBlockTypeHandle() != BLOCK_HANDLE_LAYOUT_PROXY) {
    ?>
    <div class="<?php echo $css->getContainerClass(); ?>"
    <?php if ($css->getCustomStyleID()) { ?>
    id="<?php echo $css->getCustomStyleID(); ?>"
    <?php } ?>
    <?php if ($css->getCustomStyleElementAttribute()) { ?>
    <?php echo $css->getCustomStyleElementAttribute(); ?>
    <?php } ?>
    >
    <?php
    }
