<?php

defined('C5_EXECUTE') or die("Access Denied.");

if ($blockType->getBlockTypeHandle() === BLOCK_HANDLE_CONTAINER_PROXY) { ?>

    </concrete-container>

<?php } else { ?>

    </concrete-block>
<?php } ?>

<?php
View::element('block_footer_view', ['a' => $a, 'b' => $b, 'c' => $c, 'pt' => $pt]);
?>

<?php
View::element('block_area_block_target', [
        'a' => $a,
        'afterBlockId' => $b->getBlockID(),
        'targetIndex' => ((int) $b->getBlockDisplayOrder()) + 1,
]);
?>

