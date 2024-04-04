<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\libs\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\Button;

/* @var $module Module */
/* @var $space Space */
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
        <?= $module->getContentContainerName($space) ?>
        <br><small><?= Yii::t('AdminModule.base', 'Version') . ' ' . $module->getVersion() ?></small>
    </div>
    <div class="col-xs-6 col-sm-5 col-md-6"><?= $module->getDescription() ?></div>
    <div class="col-xs-5 col-sm-3 module-actions">
        <?= Button::asLink(Yii::t('SpaceModule.manage', 'Enable'))
            ->cssClass('btn btn-sm btn-info enable')
            ->style($space->moduleManager->isEnabled($module->id) ? 'display:none' : '')
            ->loader()
            ->options([
                'data-action-click' => 'content.container.enableModule',
                'data-action-url' => $space->createUrl('/space/manage/module/enable', ['moduleId' => $module->id]),
            ]) ?>
        <?= Button::asLink(Yii::t('ContentModule.base', 'Enabled'))
            ->icon('check')
            ->cssClass('btn btn-sm btn-info active disable')
            ->style(!$space->moduleManager->isEnabled($module->id) ? 'display:none' : '')
            ->loader()
            ->options([
                'data-action-click' => 'content.container.disableModule',
                'data-action-url' => $space->createUrl('/space/manage/module/disable', ['moduleId' => $module->id]),
            ]) ?>
    </div>
</div>
