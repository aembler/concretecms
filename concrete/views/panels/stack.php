<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
use Concrete\Core\Attribute\Set as AttributeSet;

$cp = new Permissions($c);
?>
<section>
    <header><h5><?= t('Stack Settings') ?></h5></header>
    <menu>
        <?php if ($cp->canEditPageProperties()) {
            ?>
            <li>
                <a href="#" data-launch-panel-detail="stack-properties"
                   data-panel-detail-url="<?= URL::to('/ccm/system/panels/details/stack/properties') ?>"
                   data-panel-transition="fade">
                    <?= t('Properties') ?>
                </a>
            </li>
            <?php
        }
        if ($cp->canViewPageVersions()) {
            ?>
            <li>
                <a href="#" data-launch-sub-panel-url="<?= URL::to('/ccm/system/panels/page/versions') ?>">
                    <?= t('Versions') ?>
                </a>
            </li>
            <?php
        }
        if ($canEditPagePermissions) {
            ?>
            <li>
                <a href="#" data-launch-panel-detail="page-permissions"
                   data-panel-detail-url="<?= URL::to('/ccm/system/panels/details/page/permissions') ?>"
                   data-panel-transition="fade">
                    <?= t('Permissions') ?>
                </a>
            </li>
        <?php
        }

        if (!$isGlobalArea) { ?>
            <li>
                <a href="#" data-launch-panel-detail="stack-usage"
                   data-panel-detail-url="<?= URL::to('/ccm/system/panels/details/stack/usage') ?>"
                   data-panel-transition="fade">
                    <?= t('Stack Usage') ?>
                </a>
            </li>
        <?php } ?>
    </menu>
</section>
