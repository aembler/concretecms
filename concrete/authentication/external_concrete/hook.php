<?php defined('C5_EXECUTE') or die('Access denied.'); ?>

<div class="form-group">
        <span>
            <?= t('Attach your %s account', h($name)) ?>
        </span>
    <hr>
</div>
<div class="form-group">
    <a href="<?= $attachUrl ?>" class="btn btn-primary btn-success">
        <img src="<?= ASSETS_URL_IMAGES ?>/logo.svg" class="concrete-icon" />
        <?= t('Attach your %s account', h($name)) ?>
    </a>
</div>

<style>
    img.concrete-icon {
        width: 20px;
        margin-right:5px;
    }
</style>
