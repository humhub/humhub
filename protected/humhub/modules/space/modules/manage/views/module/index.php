<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ModuleCard;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\helpers\ThemeHelper;

/* @var Space $space */
/* @var ContentContainerModule[] $availableModules */
?>
<div class="<?php if (ThemeHelper::isFluid()) : ?>container-fluid<?php else: ?>container container-content-modules-col-3<?php endif; ?> container-cards container-modules container-content-modules">
    <h4><?= Yii::t('SpaceModule.manage', '<strong>Space</strong> Modules'); ?></h4>
    <div class="help-block"><?= Yii::t('SpaceModule.manage', 'Choose the modules you want to use for this Space. In order for the modules to be available to you here, they must have been previously installed by administrators of the network using the admin panel. If you cannot deactivate individual modules, it is because they have been set as the default for the entire network.') ?></div>

    <div class="row cards">
        <?php if (empty($availableModules)) : ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?= Yii::t('SpaceModule.manage', 'Currently there are no modules available for this space!'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($availableModules as $module) : ?>
            <?= ModuleCard::widget([
                'contentContainer' => $space,
                'module' => $module,
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>