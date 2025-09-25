<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
use Concrete\Core\Attribute\Set as AttributeSet;

$cp = new Permissions($c);
$pk = PermissionKey::getByHandle('edit_page_properties');
$pk->setPermissionObject($c);
$asl = $pk->getMyAssignment();
$seoSet = AttributeSet::getByHandle('seo');
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

        } ?>
    </menu>
</section>
