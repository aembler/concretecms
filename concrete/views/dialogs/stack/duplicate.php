<?php
use Concrete\Core\Page\Stack\Stack;
defined('C5_EXECUTE') or die("Access Denied.");
/** @var Concrete\Core\Page\Stack\Stack $stack */
?>

<div class="ccm-ui">
    <form method="post" data-dialog-form="delete-stack" action="<?=$controller->action('submit')?>">
        <div class="mb-3">
            <label class="form-label" for="stackName"><?= t('New Stack Name') ?></label>
            <input type="text" class="form-control" name="stackName" id="stackName" value="<?= $stack->getCollectionName() ?>">
        </div>

        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()" type="button"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end" data-dialog-action="submit" type="submit" >
            <?php
                echo t('Duplicate Stack');
            ?>
            </button>
        </div>
    </form>
</div>
