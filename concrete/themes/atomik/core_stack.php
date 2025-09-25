<?php
defined('C5_EXECUTE') or die("Access Denied.");
$view->inc('elements/header_top.php');
?>

<?php
View::element('stack/content', [
    'error' => $error ?? null, // These responses are flash messages.
    'success' => $success ?? null, // These responses are flash messages.
    'stack' => $stack,
    'breadcrumb' => $breadcrumb
]);
?>

<?php
$view->inc('elements/footer_bottom.php');
