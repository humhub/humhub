<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\activity\assets\ActivityAsset;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\widgets\ActivityBox;
use humhub\widgets\PanelMenu;

/* @var $this View */
/* @var $activities Activity[] */
/* @var $hasMore bool */
/* @var $options array */

ActivityAsset::register($this);
?>
<div class="panel panel-default panel-activities" id="panel-activities">
    <?= PanelMenu::widget() ?>
    <div class="panel-heading">
        <?= Yii::t('ActivityModule.base', '<strong>Latest</strong> activities') ?>
    </div>
    <?= Html::beginTag('div', $options) ?>
        <?php if (empty($activities)) : ?>
            <?= Yii::t('ActivityModule.base', 'There are no activities yet.') ?>
        <?php else: ?>
            <?php foreach ($activities as $activity) : ?>
                <?= ActivityBox::renderActivity($activity) ?>
            <?php endforeach; ?>
            <?php if ($hasMore) : ?>
                <div class="stream-end"></div>
            <?php endif; ?>
        <?php endif; ?>
    <?= Html::endTag('div') ?>
</div>
