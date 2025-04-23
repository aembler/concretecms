<?php

use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\Announcement\AnnouncementService;

defined('C5_EXECUTE') or die('Access Denied.');

$app = Concrete\Core\Support\Facade\Facade::getFacadeApplication();

$dh = $app->make('helper/concrete/dashboard');
$sh = $app->make('helper/concrete/dashboard/sitemap');
$request = \Concrete\Core\Http\Request::createFromGlobals();

if (isset($cp) && $cp->canViewToolbar() && (!$dh->inDashboard()) && !$view->isEditingDisabled()) {

    $cih = $app->make('helper/concrete/ui');
    $ihm = $app->make('helper/concrete/ui/menu');
    $valt = $app->make('helper/validation/token');
    $config = $app->make('config');
    $dateHelper = $app->make('helper/date');
    $token = '&' . $valt->getParameter();
    $cID = $c->getCollectionID();
    $permissions = new Permissions($c);
    $resolver = $app->make(ResolverManagerInterface::class);

    $show_titles = (bool) $config->get('concrete.accessibility.toolbar_titles');
    $show_tooltips = (bool) $config->get('concrete.accessibility.toolbar_tooltips');
    $large_font = (bool) $config->get('concrete.accessibility.toolbar_large_font');

    ?>

    <concrete-app
            toolbar-logo-src="<?=$cih->getToolbarLogoRealSrc()?>"
            toolbar-checkout-url="<?= h($resolver->resolve(["/ccm/system/page/checkout/{$cID}/-/" . $valt->generate()])) ?>?redirect=<?=h($request->getPath())?>"
            <?php if ($c->isEditMode()) { ?>
                toolbar-is-edit-mode
                toolbar-check-in-url="<?= URL::to('/ccm/system/page/check_in', $cID, $valt->generate()) ?>"
            <?php } ?>
            <?php if ($show_titles) { ?>
                toolbar-show-titles
            <?php } ?>
            <?php if ($show_tooltips) { ?>
                toolbar-show-tooltips
            <?php } ?>
            <?php if ($large_font) { ?>
                toolbar-large-font
            <?php } ?>
            toolbar-can-edit-page-contents
            toolbar-can-access-dashboard
            toolbar-can-edit-page-settings
            toolbar-color-scheme="auto"
            toolbar-dashboard-url="/dashboard"
            toolbar-can-view-sitemap

            toolbar-help-url="<?= URL::to('/ccm/system/dialogs/help/help') ?>?ccm_token=<?=$valt->generate('view_help')?>"
    ></concrete-app>


    <?php
}
