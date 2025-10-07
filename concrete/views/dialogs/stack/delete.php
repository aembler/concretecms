<?php
use Concrete\Core\Page\Stack\Stack;
defined('C5_EXECUTE') or die("Access Denied.");
/** @var Concrete\Core\Page\Stack\Stack $stack */
?>

<div class="ccm-ui">
    <form method="post" data-dialog-form="delete-stack" action="<?=$controller->action('submit')?>">
        <?php if ($stack->getStackType() === Stack::ST_TYPE_GLOBAL_AREA) { ?>
            <p><?= t('Are you sure you want to clear this global area? Its contents will be deleted. If a page containing this global area is visited, an empty stack will be re-created.') ?></p>
        <?php } else { ?>
            <p><?= t('Are you sure you want to delete this stack?') ?></p>
        <?php } ?>

        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()" type="button"><?= t('Cancel') ?></button>
            <button class="btn btn-danger float-end" data-dialog-action="submit" type="submit" >
            <?php if ($stack->getStackType() === Stack::ST_TYPE_GLOBAL_AREA) {
                echo t('Clear Global Area');
            } else {
                echo t('Delete Stack');
            } ?>
            </button>
        </div>
    </form>
</div>
