<?php
defined('C5_EXECUTE') or die('Access Denied.');
?>
<div>

    <section class="ccm-ui">
        <form method="post" action="<?= $controller->action('submit') ?>"  data-dialog-form="stack-properties" data-panel-detail-form="stack-properties">

            <span class="float-end text-muted"><?= t('Stack ID: %s', $c->getCollectionID()) ?></span>
            <h3 class="mb-3">Properties</h3>

            <div class="mb-3">
                <label for="stackName" class="form-label"><?= t('Name') ?></label>
                <?php if ($canEditName) { ?>
                <div>
                    <input type="text" class="form-control" id="stackName" name="stackName"
                           value="<?= htmlentities($c->getCollectionName(), ENT_QUOTES, APP_CHARSET) ?>"/>
                </div>
                <?php } else {
                    echo '<div>' . htmlentities($c->getCollectionName(), ENT_QUOTES, APP_CHARSET) . '</div>';
                } ?>
            </div>

        </form>
        <?php if ($canEditName) { ?>

            <div class="ccm-panel-detail-form-actions dialog-buttons">
                <button class="float-start btn btn-secondary" type="button" data-dialog-action="cancel"
                        data-panel-detail-action="cancel"><?= t('Cancel') ?></button>
                <button class="float-end btn btn-primary" type="button" data-dialog-action="submit"
                        data-panel-detail-action="submit"><?= t('Save Changes') ?></button>
            </div>
        <?php } else { ?>

            <div class="ccm-panel-detail-form-actions dialog-buttons">
                <button class="float-start btn btn-secondary" type="button" data-dialog-action="cancel"
                        data-panel-detail-action="cancel"><?= t('Back') ?></button>
            </div>
        <?php } ?>

    </section>
</div>
<script type="text/javascript">
    $(function() {
        ConcreteEvent.unsubscribe('AjaxFormSubmitSuccess.saveStackProperties');
        ConcreteEvent.subscribe('AjaxFormSubmitSuccess.saveStackProperties', function(e, data) {
            if (data.form == 'stack-properties') {
                ConcretePanelManager.exitPanelMode()
            }
        });
    });
</script>
