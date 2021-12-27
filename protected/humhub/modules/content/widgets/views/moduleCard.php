<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\libs\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\ModuleActionButtons;

/* @var Module $module */
/* @var ContentContainerActiveRecord $contentContainer */
?>
<div class="card-panel">
    <div class="card-header">
        <?= Html::img($module->getImage(), [
            'class' => 'media-object img-rounded',
            'data-src' => 'holder.js/94x94',
            'alt' => '94x94',
            'style' => 'width:94px;height:94px',
        ]) ?>
    </div>
    <div class="card-body">
        <div class="card-title"><?= $module->getName() ?></div>
    </div>
    <?= ModuleActionButtons::widget([
        'module' => $module,
        'contentContainer' => $contentContainer,
    ]) ?>
</div>