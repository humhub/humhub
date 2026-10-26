<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\table\ColumnDefinition;
use humhub\helpers\Html;

/**
 * The placeholder of a data table island ({@see \humhub\widgets\DataTableWidget}): the card with
 * skeleton rows, in the markup and column widths of `DataTable.vue`'s own skeleton, so the page
 * does not jump when the island mounts.
 *
 * @var string $title
 * @var bool $embedded inside another card
 * @var string|array|null $backUrl
 * @var string|null $backLabel
 * @var ColumnDefinition[] $columns the shown optional columns
 */
$others = count(array_filter($columns, static fn(ColumnDefinition $column) => !$column->narrow));
$mainWidth = $others > 0 ? round(200 / ($others + 2), 4) . '%' : null;
?>
<section class="content-card data-table <?= $embedded ? 'data-table-embedded' : 'card' ?>" aria-busy="true">
    <?php if ($title !== '') : ?>
        <div class="card-header">
            <?php if ($backUrl !== null) : ?>
                <?= $this->render('_backLink', ['url' => $backUrl, 'label' => $backLabel, 'class' => 'card-back']) ?>
            <?php endif; ?>
            <?= Html::tag($embedded ? 'h3' : 'h2', Html::encode($title), ['class' => 'card-title']) ?>
        </div>
    <?php endif; ?>
    <div class="card-body">
        <div class="data-table-wrap">
            <table class="table" aria-hidden="true">
                <colgroup>
                    <col class="data-table-col-main"<?= $mainWidth ? ' style="width: ' . $mainWidth . '"' : '' ?>>
                    <?php foreach ($columns as $column) : ?>
                        <col<?= $column->narrow ? ' class="data-table-col-narrow"' : '' ?>>
                    <?php endforeach; ?>
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
                        <?php for ($c = 0; $c < count($columns); $c++) : ?>
                            <td><span class="placeholder col-6"></span></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
