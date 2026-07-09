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
<div class="panel panel-default panel-activities overflow-hidden" id="panel-activities">
    <?= PanelMenu::widget() ?>
    <div class="panel-heading">
        <?= Yii::t('ActivityModule.base', '<strong>Latest</strong> activities') ?>
    </div>
    <div class="panel-body p-0">
    <?= Html::beginTag('div', $options) ?>
        <hr class="m-0">
        <?php if (empty($activities)) : ?>
            <p class="p-3 m-0"><?= Yii::t('ActivityModule.base', 'There are no activities yet.') ?></p>
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
</div>
