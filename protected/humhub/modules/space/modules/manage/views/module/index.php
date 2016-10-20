<?php

use yii\helpers\Html;

?><div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_modules', '<strong>Space</strong> Modules'); ?>
    </div>
    <div class="panel-body">

        <?php if (count($availableModules) == 0): ?>
            <p><?php echo Yii::t('SpaceModule.views_admin_modules', 'Currently there are no modules available for this space!'); ?></p>
        <?php else: ?>
            <?php echo Yii::t('SpaceModule.views_admin_modules', 'Enhance this space with modules.'); ?><br>
        <?php endif; ?>


        <?php foreach ($availableModules as $moduleId => $module): ?>
            <hr>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $module->getContentContainerImage($space); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getContentContainerName($space); ?>
                        <?php if ($space->isModuleEnabled($moduleId)) : ?>
                            <small><span class="label label-success"><?php echo Yii::t('SpaceModule.views_admin_modules', 'Activated'); ?></span></small>
                            <?php endif; ?>
                    </h4>

                    <p><?php echo $module->getContentContainerDescription($space); ?></p>
                    <?php if ($space->isModuleEnabled($moduleId)) : ?>

                        <?php if ($space->canDisableModule($moduleId)): ?>
                            <?php echo Html::a(Yii::t('SpaceModule.views_admin_modules', 'Disable'), $space->createUrl('/space/manage/module/disable', array('moduleId' => $moduleId)), array('data-method' => 'POST', 'class' => 'btn btn-sm btn-primary', 'data-confirm' => Yii::t('SpaceModule.views_admin_modules', 'Are you sure? *ALL* module data for this space will be deleted!'), 'data-ui-loader' => '')); ?>
                        <?php endif; ?>

                        <?php if ($module->getContentContainerConfigUrl($space) != "") : ?>
                            <?php
                            echo Html::a(
                                    Yii::t('SpaceModule.views_admin_modules', 'Configure'), $module->getContentContainerConfigUrl($space), array('class' => 'btn btn-default')
                            );
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo Html::a(Yii::t('SpaceModule.views_admin_modules', 'Enable'), $space->createUrl('/space/manage/module/enable', array('moduleId' => $moduleId)), array('data-method' => 'POST', 'class' => 'btn btn-sm btn-primary', 'data-ui-loader' => '')); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>