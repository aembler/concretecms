<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php $view->inc('elements/header.php'); ?>

<div class="mx-auto max-w-screen-xl">
    <?php
    View::element(
        'system_errors',
        array(
            'format' => 'block',
            'error' => isset($error) ? $error : null,
            'success' => isset($success) ? $success : null,
            'message' => isset($message) ? $message : null,
        )
    );
    ?>
    <?php echo $innerContent ?>
</div>

<?php $view->inc('elements/footer.php'); ?>