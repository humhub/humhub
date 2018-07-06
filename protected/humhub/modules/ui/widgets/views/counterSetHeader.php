<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;

/* @var $this \humhub\components\View */
/* @var $counters \humhub\modules\ui\widgets\CounterItem[] */

?>

<div class="statistics pull-left">
    <?php foreach ($counters as $counter): ?>

        <?php if ($counter->hasLink()): ?>
            <?= Html::beginTag('a', array_merge(['href' => $counter->url], $counter->linkOptions)); ?>
        <?php endif; ?>

        <div class="pull-left entry">
            <span class="count"><?= $counter->value; ?></span>
            <br>
            <span class="title"><?= $counter->label; ?></span>
        </div>

        <?php if ($counter->hasLink()): ?>
            <?= Html::endTag('a'); ?>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
