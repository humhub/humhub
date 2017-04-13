<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('AdminModule.views_module_list', '<strong>Modules</strong> directory'); ?></div>
    <?= $this->render('_header'); ?>
    <div class="panel-body">
        <?php if (count($installedModules) == 0): ?>
            <br>
            <div><?= Yii::t('AdminModule.module_list', 'No modules installed yet. Install some to enhance the functionality!'); ?></div>
        <?php endif; ?>

        <?php foreach ($installedModules as $moduleId => $module) : ?>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="<?= $module->getImage(); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?= $module->getName(); ?>
                        <small>
                            <?php if (Yii::$app->hasModule($module->id)) : ?>
                                <span class="label label-info"><?= Yii::t('AdminModule.module_list', 'Activated'); ?></span>
                            <?php endif; ?>
                        </small>
                    </h4>

                    <p><?= $module->getDescription(); ?></p>

                    <div class="module-controls">

                        <?= Yii::t('AdminModule.module_list', 'Version:'); ?> <?= $module->getVersion(); ?>

                        <?php if (Yii::$app->hasModule($module->id)) : ?>
                            <?php if ($module->getConfigUrl() != "") : ?>
                                &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'Configure'), $module->getConfigUrl(), ['style' => 'font-weight:bold']); ?>
                            <?php endif; ?>

                            <?php if ($module instanceof \humhub\modules\content\components\ContentContainerModule): ?>
                                &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'Set as default'), Url::to(['/admin/module/set-as-default', 'moduleId' => $moduleId]), ['data-target' => '#globalModal']); ?>
                            <?php endif; ?>

                            &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'Disable'), Url::to(['/admin/module/disable', 'moduleId' => $moduleId]), ['data-method' => 'POST', 'data-confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module data will be lost!')]); ?>

                        <?php else: ?>
                            &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'Enable'), Url::to(['/admin/module/enable', 'moduleId' => $moduleId]), ['data-method' => 'POST', 'style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_list', 'Enable module...')]); ?>
                        <?php endif; ?>

                        <?php if (Yii::$app->moduleManager->canRemoveModule($moduleId)): ?>
                            &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'Uninstall'), Url::to(['/admin/module/remove', 'moduleId' => $moduleId]), ['data-method' => 'POST', 'data-confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module related data and files will be lost!')]); ?>
                        <?php endif; ?>

                        &middot; <?= Html::a(Yii::t('AdminModule.views_module_list', 'More info'), Url::to(['/admin/module/info', 'moduleId' => $moduleId]), ['data-target' => '#globalModal']); ?>

                    </div>

                </div>
            </div>
            <hr/>
        <?php endforeach; ?>
    </div>
</div>