<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;

/* @var $this View */
/* @var $blocks [] */

?>

<div class="filter-panel col">
    <?php foreach ($blocks as $block): ?>
        <?= FilterBlock::widget($block) ?>
    <?php endforeach; ?>
</div>
