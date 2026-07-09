<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\widgets\ModuleActionButtons;
use humhub\modules\marketplace\widgets\ModuleControls;
use humhub\modules\marketplace\widgets\ModuleStatus;

/* @var Module $module */
?>
<div class="card-panel">
    <?= ModuleStatus::widget(['module' => $module]) ?>
    <div class="card-header">
        <?= $module->marketplaceImage() ?>
        <?= ModuleControls::widget(['module' => $module]) ?>
    </div>
    <div class="card-body">
        <div class="card-title"><?= $module->marketplaceName() ?></div>
        <div><?= $module->latestVersion ?></div>
        <div><?= Html::encode($module->description) ?></div>
    </div>
    <?= ModuleActionButtons::widget(['module' => $module]) ?>
</div>
