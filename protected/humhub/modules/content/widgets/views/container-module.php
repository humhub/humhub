<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModuleActionButtons;

/* @var ContentContainerModule $module */
/* @var ContentContainerActiveRecord $contentContainer */
?>
<div class="module-row row">
    <div class="col-xs-2 col-sm-1 module-icon">
        <?= Html::img($module->getImage(), [
            'class' => 'media-object img-rounded',
            'data-src' => 'holder.js/34x34',
            'alt' => '34x34',
            'style' => 'width:34px;height:34px',
        ]) ?>
    </div>
    <div class="col-xs-10 col-sm-3 col-md-2">
        <?= $module->getContentContainerName($contentContainer) ?>
        <br><small><?= Yii::t('AdminModule.base', 'Version') . ' ' . $module->getVersion() ?></small>
    </div>
    <div class="col-xs-6 col-sm-5 col-md-6"><?= $module->getContentContainerDescription($contentContainer) ?></div>
    <div class="col-xs-5 col-sm-3 module-actions">
        <?= ContainerModuleActionButtons::widget([
            'module' => $module,
            'contentContainer' => $contentContainer,
        ]) ?>
    </div>
</div>
