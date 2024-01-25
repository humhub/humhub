<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModule;
use humhub\modules\space\models\Space;
use yii\web\View;

/* @var View $this */
/* @var Space $space */
/* @var ContentContainerModule[] $availableModules */
?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('SpaceModule.manage', '<strong>Space</strong> Modules') ?>
        <div class="form-text"><?= Yii::t('SpaceModule.manage', 'Choose the modules you want to use for this Space. In order for the modules to be available to you here, they must have been previously installed by administrators of the network using the admin panel. If you cannot deactivate individual modules, it is because they have been set as the default for the entire network.') ?></div>
    </div>

    <div class="card-body">
        <?php if (empty($availableModules)): ?>
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-body">
                        <?= Yii::t('SpaceModule.manage', 'Currently there are no modules available for this space!'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="modules-group">
            <?php foreach ($availableModules as $module) : ?>
                <?= ContainerModule::widget([
                    'contentContainer' => $space,
                    'module' => $module,
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
