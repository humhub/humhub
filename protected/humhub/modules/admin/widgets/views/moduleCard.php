<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\libs\Html;
use humhub\modules\admin\widgets\ModuleActionButtons;
use humhub\modules\admin\widgets\ModuleControls;
use humhub\modules\admin\widgets\ModuleStatus;
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
    <?= ModuleActionButtons::widget(['module' => $module]) ?>
</div>