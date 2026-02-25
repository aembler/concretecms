<?php
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Block\BlockType\BlockType;
$blockTypeService = app(BlockType::class);
?>

<legend><?=$category->getItemCategoryDisplayName()?></legend>

<ul id="ccm-block-type-package-list" class="item-select-list">
    <?php foreach ($category->getItems($package) as $bt) {
        $icon = $blockTypeService->getBlockTypeIcon($bt);
        ?>
        <li>
            <a href="<?= $view->url('/dashboard/blocks/types', 'inspect', $bt->getBlockTypeID());
            ?>"><?= $icon->toHtmlObject() ?> <?=t($bt->getBlockTypeName());
                ?></a>
        </li>
        <?php
    }
    ?>
</ul>
