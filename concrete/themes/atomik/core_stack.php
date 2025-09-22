<?php
defined('C5_EXECUTE') or die("Access Denied.");
$view->inc('elements/header_top.php');
?>

<?php
View::element('stack/content', ['stack' => $stack, 'breadcrumb' => $breadcrumb]);
?>

<?php
$view->inc('elements/footer_bottom.php');
