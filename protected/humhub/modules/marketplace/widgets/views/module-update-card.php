<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\marketplace\assets\Assets;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\widgets\ModuleUpdateActionButtons;
use humhub\modules\ui\view\components\View;

/* @var View $this */
/* @var Module $module */

Assets::register($this);
?>
<div class="card-panel">
    <div class="card-header">
        <?= Html::img($module->image, [
            'class' => 'media-object img-rounded',
            'data-src' => 'holder.js/60x60',
            'alt' => '60x60',
            'style' => 'width:60px;height:60px',
        ]) ?>
    </div>
    <div class="card-body">
        <div class="card-title"><?= $module->name ?></div>
        <div><?= Yii::$app->moduleManager->getModule($module->id)->getVersion() ?> → <?= $module->latestCompatibleVersion ?></div>
    </div>
    <?= ModuleUpdateActionButtons::widget(['module' => $module]) ?>
</div>