<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\libs\Helpers;
use humhub\libs\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\ModuleActionButtons;

/* @var $module Module */
/* @var $contentContainer Space */
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
        <div><?= Helpers::truncateText($module->getContentContainerDescription($contentContainer), 75); ?></div>
    </div>
    <?= ModuleActionButtons::widget(['module' => $module, 'space' => $contentContainer]) ?>
</div>