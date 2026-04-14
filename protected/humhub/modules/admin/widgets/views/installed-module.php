<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\helpers\Html;
use humhub\modules\admin\widgets\InstalledModuleActionButtons;
use humhub\modules\admin\widgets\InstalledModuleControls;

/* @var $module Module */
?>
<div class="container gx-0 overflow-x-hidden">
    <div class="module-row row">
        <div class="col-2 col-md-1 ps-0 module-icon">
            <?= Html::img($module->getImage(), [
                'class' => 'rounded',
                'data-src' => 'holder.js/34x34',
                'alt' => '34x34',
                'style' => 'width:34px;height:34px',
            ]) ?>
        </div>
        <div class="col-10 col-md-3 ps-0 col-lg-2">
            <?= $module->getName() ?>
            <br><small><?= Yii::t('AdminModule.base', 'Version') . ' ' . $module->getVersion() ?></small>
        </div>
        <div class="col-6 col-md-5 col-lg-6 ps-0"><?= $module->getDescription() ?></div>
        <div class="col-6 col-md-3 pe-0 module-actions">
            <?= InstalledModuleActionButtons::widget(['module' => $module]) ?>
            <?= InstalledModuleControls::widget(['module' => $module]) ?>
        </div>
    </div>
</div>
