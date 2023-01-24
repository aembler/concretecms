
<div class="form-group">
        <span>
            <?= t('Attach a community account') ?>
        </span>
    <hr>
</div>
<div class="form-group">
    <a href="<?= \URL::to('/ccm/system/authentication/oauth2/community/attempt_attach'); ?>" class="btn btn-primary btn-community">
        <img src="<?= ASSETS_URL_IMAGES ?>/logo.svg" class="concrete-icon" />
        <?= t('Attach a community.concretecms.com account') ?>
    </a>
</div>

<style>
    img.concrete-icon {
        width: 20px;
        margin-right:5px;
    }
</style>
