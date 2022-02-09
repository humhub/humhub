<?php

use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ModuleCard;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\helpers\ThemeHelper;

/* @var Space $space */
/* @var ContentContainerModule[] $modules */

ModuleAsset::register($this);
?>
<div class="<?php if (ThemeHelper::isFluid()) : ?>container-fluid<?php else: ?>container container-content-modules-col-3<?php endif; ?> container-cards container-modules container-content-modules">
    <h4><?= Yii::t('SpaceModule.manage', '<strong>Space</strong> Modules'); ?></h4>

    <div class="row cards">
        <?php if (empty($modules)) : ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?= Yii::t('SpaceModule.manage', 'Currently there are no modules available for this space!'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($modules as $module) : ?>
            <?= ModuleCard::widget([
                'contentContainer' => $space,
                'module' => $module,
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>