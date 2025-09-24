<?php
defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Block\View\BlockView;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Workflow\Progress\PageProgress as PageWorkflowProgress;

/**
 * @var Concrete\Core\Html\Service\Html $html
 * @var Concrete\Core\Application\Service\Composer $composer
 * @var Concrete\Core\Application\Service\Dashboard $dashboard
 * @var Concrete\Core\Application\Service\UserInterface $interface
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Controller\SinglePage\Dashboard\Blocks\Stacks $controller
 * @var Concrete\Core\Page\Page $c
 * @var string $localeName
 * @var string $localeCode
 * @var array $blocks
 */


    if (!isset($showGlobalAreasFolder)) {
        $showGlobalAreasFolder = false;
    }
    if (!isset($canMoveStacks)) {
        $canMoveStacks = false;
    }
/**
 * @var Concrete\Core\Page\Stack\StackList $list
 * @var Concrete\Core\Page\Page[] $stacks
 */
if ($showGlobalAreasFolder || !empty($stacks)) {
    $dh = Core::make('date');
    ?>
    <div class="table-responsive">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th></th>
                <th class="<?= $list->getSortClassName('cv.cvName') ?>">
                    <a href="<?= h($list->getSortURL('cv.cvName')) ?>"><?= t('Name') ?></a>
                </th>
                <th class="<?= $list->getSortClassName('c.cDateAdded') ?>">
                    <a href="<?= h($list->getSortURL('c.cDateAdded')) ?>"><?= t('Date Added') ?></a>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($showGlobalAreasFolder) {
                ?>
                <tr class="ccm-search-results-folder ccm-search-results-globalareafolder" data-details-url="<?= $view->action('view_global_areas') ?>">
                    <td class="ccm-search-results-icon"><i class="fas fa-object-group"></i></td>
                    <td class="ccm-search-results-name"><?= t('Global Areas') ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php
            }
            foreach ($stacks as $st) {
                $formatter = new Concrete\Core\Page\Stack\Formatter($st);
                ?>
                <tr class="<?= $formatter->getSearchResultsClass() ?>"
                    <?php if ($st->getPageTypeHandle() === STACK_CATEGORY_PAGE_TYPE) { ?>
                        data-details-url="<?= $view->action('view_details', $st->getCollectionID()) ?>"
                    <?php } else { ?>
                        data-details-url="<?= $st->getCollectionLink() ?>"
                    <?php } ?>
                    data-collection-id="<?= $st->getCollectionID() ?>">
                    <td class="ccm-search-results-icon"><?= $formatter->getIconElement() ?></td>
                    <td class="ccm-search-results-name"><?= h($st->getCollectionName()) ?></td>
                    <td><?= $dh->formatDateTime($st->getCollectionDateAdded()) ?></td>
                    <td class="ccm-search-results-menu-launcher">
                        <?php if ($st->getPageTypeHandle() === STACK_CATEGORY_PAGE_TYPE) { ?>
                            <div class="dropdown">
                                <button class="btn btn-icon" data-boundary="viewport" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <svg width="16" height="4"><use xlink:href="#icon-menu-launcher"/></svg>
                                </button>
                                <div class="dropdown-menu">
                                    <a data-action="rename" class="dropdown-item" href="<?= $view->action('rename', $st->getCollectionID()) ?>">
                                        <?= t('Rename Folder') ?>
                                    </a>
                                    <a data-action="delete" class="dropdown-item" data-folder-id="<?= $st->getCollectionID() ?>" href="javascript:void(0)">
                                        <?= t('Delete Folder') ?>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">
        $(function () {
            var $tbody = $('table.ccm-search-results-table tbody');
            $('.ccm-search-results-menu-launcher a[data-action=delete]').on('click', function () {
                var folderID = $(this).data('folder-id'),
                    $dialog = $('#ccm-dialog-delete-stackfolder');
                $dialog.find('input[name=stackfolderID]').val(folderID);
                jQuery.fn.dialog.open({
                    element: '#ccm-dialog-delete-stackfolder',
                    modal: true,
                    width: 'auto',
                    title: <?= json_encode(t('Delete Folder')) ?>,
                    height: 200
                });
            });
            $tbody.find('>tr').each(function () {
                var $this = $(this), className = $this.attr('class');
                $this
                <?php if ($canMoveStacks) { ?>
                    .not('.ccm-search-results-globalareafolder')
                    .draggable({
                        delay: 300,
                        start: function () {
                            $this.addClass('ccm-stack-folder-dragging');
                            $('.ccm-undroppable-search-item').css('opacity', '0.4');
                        },
                        stop: function () {
                            $('.ccm-undroppable-search-item').css('opacity', '');
                        },
                        helper: function () {
                            var $selected = $this.add($tbody.find('.ccm-stack-folder-dragging'));
                            return $('<div class="' + className + ' ccm-draggable-search-item"><span><i class="fa fa-share"></i></span></div>').data('$selected', $selected);
                        },
                        cursorAt: {
                            left: -20,
                            top: 5
                        }
                    })
                    .end()
                <?php } ?>
                ;
            });
            <?php if ($canMoveStacks) { ?>
            $('.ccm-droppable-search-item').droppable({
                accept: '.ccm-search-results-stackfolder, .ccm-search-results-stack',
                //activeClass: 'ui-state-highlight',
                hoverClass: 'ccm-search-select-active-droppable',
                drop: function (event, ui) {
                    var $sourceItems = ui.helper.data('$selected'),
                        sourceIDs = [],
                        destinationID = $(this).data('collection-id')
                    ;
                    $sourceItems.each(function () {
                        var $sourceItem = $(this);
                        var sourceID = $sourceItem.data('collection-id');
                        if (sourceID == destinationID) {
                            $sourceItems = $sourceItems.not(this);
                        } else {
                            sourceIDs.push($(this).data('collection-id'));
                        }
                    });
                    if (sourceIDs.length === 0) {
                        return;
                    }
                    $sourceItems.hide();
                    new ConcreteAjaxRequest({
                        url: <?=json_encode($view->action('move_to_folder'))?>,
                        data: {
                            ccm_token:<?=json_encode($token->generate('move_to_folder'))?>,
                            sourceIDs: sourceIDs,
                            destinationID: destinationID
                        },
                        success: function (msg) {
                            $sourceItems.remove();
                            ConcreteAlert.notify({
                                message: msg
                            });
                        },
                        error: function (xhr) {
                            $sourceItems.show();
                            var msg = xhr.responseText;
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = xhr.responseJSON.errors.join("<br/>");
                            }
                            ConcreteAlert.dialog(<?=json_encode(t('Error'))?>, msg);
                        }
                    });
                }
            });
            <?php } ?>
        });
    </script>

    <div style="display: none">
        <div id="ccm-dialog-delete-stackfolder" class="ccm-ui" title="<?= t('Delete Folder') ?>">
            <form method="post" class="form-stacked" style="padding-left: 0" action="<?= $view->action('delete_stackfolder') ?>">
                <?php $token->output('delete_stackfolder') ?>
                <input type="hidden" name="stackfolderID"/>
                <p><?= t('Are you sure? This action cannot be undone.'); ?></p>
            </form>
            <div class="dialog-buttons">
                <button class="btn btn-secondary" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                <button class="btn btn-danger ms-auto" onclick="$('#ccm-dialog-delete-stackfolder form').submit()">
                    <?= t('Delete Folder') ?>
                </button>
            </div>
        </div>
    </div>

    <?php
} else {
    ?>
    <div id="ccm-dashboard-content-regular">
        <div class="alert alert-info"><?php
            if ($controller->getAction() == 'view_global_areas') {
                echo t('No global areas have been added.');
            } else {
                echo t('No stacks found in this folder.');
            }
            ?>
        </div>
    </div>
    <?php
}
    ?>

    <div class="ccm-dashboard-header-buttons">
        <?php
        if ($controller->getAction() != 'view_global_areas') {
            ?>
            <div class="btn-group">
                <button data-dialog="add-stack" class="btn btn-secondary"><i class="fas fa-bars"></i> <?= t('New Stack') ?></button>
                <button data-dialog="add-folder" class="btn btn-secondary"><i class="fas fa-folder"></i> <?= t('New Folder') ?></button>
            </div>
            <?php
        }
        ?>
    </div>

    <div style="display: none">
        <div id="ccm-dialog-add-stack" class="ccm-ui">
            <form method="post" class="form-stacked" style="padding-left: 0"
                  action="<?= $view->action('add_stack') ?>">
                <?= $token->output('add_stack') ?>
                <?= $form->hidden('stackFolderID', $currentStackFolderID ?? ''); ?>
                <div class="form-group">
                    <?= $form->label('stackName', t('Stack Name')) ?>
                    <?= $form->text('stackName') ?>
                </div>
            </form>
            <div class="dialog-buttons">
                <button class="btn btn-secondary me-auto"
                        onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                <button class="btn btn-primary float-end"
                        onclick="$('#ccm-dialog-add-stack form').submit()"><?= t('Add Stack') ?></button>
            </div>
        </div>
        <div id="ccm-dialog-add-folder" class="ccm-ui">
            <form method="post" class="form-stacked" style="padding-left: 0"
                  action="<?= $view->action('add_folder') ?>">
                <?= $token->output('add_folder') ?>
                <?= $form->hidden('stackFolderID', $currentStackFolderID ?? ''); ?>
                <div class="form-group">
                    <?= $form->label('folderName', t('Folder Name')) ?>
                    <?= $form->text('folderName') ?>
                </div>
            </form>
            <div class="dialog-buttons">
                <button class="btn btn-secondary me-auto" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                <button class="btn btn-primary float-end" onclick="$('#ccm-dialog-add-folder form').submit()"><?= t('Add Folder') ?></button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $('button[data-dialog=add-stack]').on('click', function () {
                jQuery.fn.dialog.open({
                    element: '#ccm-dialog-add-stack',
                    modal: true,
                    width: 900,
                    title: <?=json_encode(t('Add Stack'))?>,
                    height: 'auto'
                });
            });

            $('button[data-dialog=add-folder]').on('click', function () {
                jQuery.fn.dialog.open({
                    element: '#ccm-dialog-add-folder',
                    modal: true,
                    width: 320,
                    title: <?=json_encode(t('Add Folder'))?>,
                    height: 'auto'
                });
            });
        });
    </script>
    <?php


if (isset($flashMessage)) {
    ?>
    <script>
        $(document).ready(function () {
            ConcreteAlert.notify({
                message: <?=json_encode($flashMessage)?>
            });
        });
    </script><?php
}
