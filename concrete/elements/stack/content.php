<?php

defined('C5_EXECUTE') or die('Access Denied.');

?>

<div class="pt-6 pb-6 container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <?=$breadcrumb->render()?>
                </div>
                <div class="card-body">
                    <?php
                    $a = new Area('Main');
                    $a->setAreaGridMaximumColumns(12);
                    $a->display($c);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

