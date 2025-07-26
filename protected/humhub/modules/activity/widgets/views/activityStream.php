<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences
 */

use humhub\helpers\Html;
use humhub\modules\activity\assets\ActivityAsset;
use humhub\widgets\PanelMenu;

/* @var $this \humhub\components\View */
/* @var $streamUrl string */
/* @var $options array */

ActivityAsset::register($this);
?>
<div class="panel panel-default panel-activities" id="panel-activities">
    <?= PanelMenu::widget(['collapseId' => 'panel-activities-content']) ?>
    <div class="panel-heading">
        <?= Yii::t('ActivityModule.base', '<strong>Latest</strong> activities') ?>
    </div>
    <div class="collapse" id="panel-activities-content">
        <?= Html::beginTag('div', $options) ?>
        <div id="activityContents" class="hh-list activities" data-stream-content></div>
        <?= Html::endTag('div') ?>
    </div>
</div>
