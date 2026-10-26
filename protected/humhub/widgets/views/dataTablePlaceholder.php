<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;

/**
 * The placeholder of a data table island ({@see \humhub\widgets\DataTableWidget}): the card with
 * skeleton rows, in the markup and column widths of `DataTable.vue`'s own skeleton, so the page
 * does not jump when the island mounts.
 *
 * @var string $title
 * @var int $columns the shown optional columns
 */
$mainWidth = $columns > 0 ? round(200 / ($columns + 2), 4) . '%' : null;
?>
<section class="card content-card data-table" aria-busy="true">
    <?php if ($title !== '') : ?>
        <div class="card-header">
            <h2 class="card-title"><?= Html::encode($title) ?></h2>
        </div>
    <?php endif; ?>
    <div class="card-body">
        <div class="data-table-wrap">
            <table class="table" aria-hidden="true">
                <colgroup>
                    <col class="data-table-col-main"<?= $mainWidth ? ' style="width: ' . $mainWidth . '"' : '' ?>>
                    <?php for ($c = 0; $c < $columns; $c++) : ?>
                        <col>
                    <?php endfor; ?>
                </colgroup>
                <tbody>
                <?php for ($i = 0; $i < 5; $i++) : ?>
                    <tr class="data-table-skeleton placeholder-glow">
                        <td class="data-table-col-main">
                            <div class="data-table-main">
                                <span class="placeholder data-table-main-image"></span>
                                <span class="data-table-main-text">
                                    <span class="placeholder col-8"></span>
                                    <span class="placeholder placeholder-sm col-6"></span>
                                </span>
                            </div>
                        </td>
                        <?php for ($c = 0; $c < $columns; $c++) : ?>
                            <td><span class="placeholder col-6"></span></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
