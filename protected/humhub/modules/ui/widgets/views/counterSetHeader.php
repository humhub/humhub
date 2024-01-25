<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $counters \humhub\modules\ui\widgets\CounterSetItem[] */

?>

<div class="statistics float-start">
    <?php foreach ($counters as $counter): ?>

        <?php if ($counter->hasLink()): ?>
            <?= Html::beginTag('a', array_merge(['href' => $counter->url], $counter->linkOptions)); ?>
        <?php endif; ?>

        <div class="float-start entry">
            <span class="count"><?= $counter->getShortValue(); ?></span>
            <br>
            <span class="title"><?= $counter->label; ?></span>
        </div>

        <?php if ($counter->hasLink()): ?>
            <?= Html::endTag('a'); ?>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
