<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var \Concrete\Core\Entity\Statistics\UsageTracker\StackUsageRecord[] $records
 */
?>
<div>

    <section class="ccm-ui">
        <h3 class="mb-3"><?= t('Stack Usage') ?></h3>

        <?php
        $hasRecords = false;

        /** @var \Concrete\Core\Page\Collection\Collection $page */
        foreach ($records

                 as $page) {
        if (!$hasRecords) {
        // First record → output the table start + header
        $hasRecords = true;
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <td><?= t('Page ID') ?></td>
                <td><?= t('Version') ?></td>
                <td><?= t('Handle') ?></td>
                <td><?= t('Location') ?></td>
            </tr>
            </thead>
            <?php
            }
            ?>
            <tr>
                <td><?= $page->getCollectionID() ?></td>
                <td><?= $page->getVersionID() ?></td>
                <td><?= $page->getCollectionHandle() ?></td>
                <td>
                    <a target="_blank" href="<?= \URL::to($page) ?>">
                        <?= h($page->getCollectionPath() ?: '/') ?>
                    </a>
                </td>
            </tr>
            <?php
            }

            // after looping, check if we ever had records
            if ($hasRecords) {
            ?>
        </table>
    <?php
    } else {
        ?>
        <p><?= t('This stack is not used on any pages.') ?></p>
        <?php
    }
    ?>

    </section>
</div>