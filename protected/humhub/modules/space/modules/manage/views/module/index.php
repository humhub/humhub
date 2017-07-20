<?php

use yii\helpers\Html;
?><div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.views_admin_modules', '<strong>Space</strong> Modules'); ?>
    </div>
    <div class="panel-body">

        <?php if (count($availableModules) == 0): ?>
            <p><?= Yii::t('SpaceModule.views_admin_modules', 'Currently there are no modules available for this space!'); ?></p>
        <?php else: ?>
            <?= Yii::t('SpaceModule.views_admin_modules', 'Enhance this space with modules.'); ?><br>
        <?php endif; ?>


        <?php foreach ($availableModules as $moduleId => $module): ?>
            <hr>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?= $module->getContentContainerImage($space); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?= $module->getContentContainerName($space); ?>
                        <?php if ($space->isModuleEnabled($moduleId)) : ?>
                            <small><span class="label label-success"><?= Yii::t('SpaceModule.views_admin_modules', 'Activated'); ?></span></small>
                        <?php endif; ?>
                    </h4>

                    <p><?= $module->getContentContainerDescription($space); ?></p>

                    <?php if ($space->canDisableModule($moduleId)): ?>
                        <a href="#" style="<?= $space->isModuleEnabled($moduleId) ? '' : 'display:none' ?>"
                           data-action-click="content.container.disableModule" 
                           data-action-url="<?= $space->createUrl('/space/manage/module/disable', ['moduleId' => $moduleId]) ?>" data-reload="1"
                           data-action-confirm="<?= Yii::t('SpaceModule.views_admin_modules', 'Are you sure? *ALL* module data for this space will be deleted!') ?>"
                           class="btn btn-sm btn-primary disable disable-module-<?= $moduleId ?>" data-ui-loader>
                               <?= Yii::t('SpaceModule.views_admin_modules', 'Disable') ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($module->getContentContainerConfigUrl($space) && $space->isModuleEnabled($moduleId)) : ?>
                        <a href="<?= $module->getContentContainerConfigUrl($space) ?>" class="btn btn-sm btn-default configure-module-<?= $moduleId ?>">
                            <?= Yii::t('SpaceModule.views_admin_modules', 'Configure') ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="#"  style="<?= $space->isModuleEnabled($moduleId) ? 'display:none' : '' ?>"
                       data-action-click="content.container.enableModule" data-action-url="<?= $space->createUrl('/space/manage/module/enable', ['moduleId' => $moduleId]) ?>" data-reload="1"
                       class="btn btn-sm btn-primary enable enable-module-<?= $moduleId ?>" data-ui-loader>
                        <?= Yii::t('SpaceModule.views_admin_modules', 'Enable') ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>