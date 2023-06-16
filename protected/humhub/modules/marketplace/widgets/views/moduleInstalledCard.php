<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
use humhub\components\Module;
use humhub\libs\Html;
use humhub\modules\marketplace\widgets\ModuleControls;
use humhub\modules\marketplace\widgets\ModuleInstalledActionButtons;
use humhub\modules\marketplace\widgets\ModuleStatus;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $module Module */
/* @var $isFeaturedModule bool */
?>
<div class="card-panel">
    <?= ModuleStatus::widget(['module' => $module]) ?>
    <div class="card-header">
        <?= Html::img($module->getImage(), [
            'class' => 'media-object img-rounded',
            'data-src' => 'holder.js/94x94',
            'alt' => '94x94',
            'style' => 'width:94px;height:94px',
        ]) ?>
        <?= ModuleControls::widget(['module' => $module]) ?>
    </div>
    <div class="card-body">
        <div class="card-title"><?= $module->getName() . ($isFeaturedModule ? ' ' . Icon::get('star')->color('info') : '') ?></div>
        <div><?= $module->getVersion() ?></div>
        <div><?= $module->getDescription() ?></div>
    </div>
    <?= ModuleInstalledActionButtons::widget(['module' => $module]) ?>
</div>
