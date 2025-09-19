<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\modules\ui\filter\widgets\FilterBlock;

/* @var $this View */
/* @var $span int */
/* @var $blocks [] */

$colSpan = $span <= 4 ? 12 / $span : 6;

?>

<div class="filter-panel col-lg-<?= $colSpan ?>">
    <?php foreach ($blocks as $block): ?>
        <?= FilterBlock::widget($block) ?>
    <?php endforeach; ?>
</div>
