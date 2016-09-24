<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_list', '<strong>Modules</strong> directory'); ?></div>
    <?php echo $this->render('_header'); ?>
    <div class="panel-body">
        <?php if (count($installedModules) == 0): ?>
            <br>
            <div><?php echo Yii::t('AdminModule.module_list', 'No modules installed yet. Install some to enhance the functionality!'); ?></div>
        <?php endif; ?>

        <?php foreach ($installedModules as $moduleId => $module) : ?>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $module->getImage(); ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module->getName(); ?>
                        <small>
                            <?php if (Yii::$app->hasModule($module->id)) : ?>
                                <span class="label label-success"><?php echo Yii::t('AdminModule.module_list', 'Activated'); ?></span>
                            <?php endif; ?>
                        </small>
                    </h4>


                    <p><?php echo $module->getDescription(); ?></p>

                    <div class="module-controls">

                        <?php echo Yii::t('AdminModule.module_list', 'Version:'); ?> <?php echo $module->getVersion(); ?>

                        <?php if (Yii::$app->hasModule($module->id)) : ?>
                            <?php if ($module->getConfigUrl() != "") : ?>
                                &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'Configure'), $module->getConfigUrl(), array('style' => 'font-weight:bold')); ?>
                            <?php endif; ?>

                            <?php if ($module instanceof \humhub\modules\content\components\ContentContainerModule): ?>
                                &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'Set as default'), Url::to(['/admin/module/set-as-default', 'moduleId' => $moduleId]), array('data-target' => '#globalModal')); ?>
                            <?php endif; ?>

                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'Disable'), Url::to(['/admin/module/disable', 'moduleId' => $moduleId]), array('data-method' => 'POST', 'data-confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module data will be lost!'))); ?>

                        <?php else: ?>
                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'Enable'), Url::to(['/admin/module/enable', 'moduleId' => $moduleId]), array('data-method' => 'POST', 'style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_list', 'Enable module...'))); ?>
                        <?php endif; ?>

                        <?php if (Yii::$app->moduleManager->canRemoveModule($moduleId)): ?>
                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'Uninstall'), Url::to(['/admin/module/remove', 'moduleId' => $moduleId]), array('data-method' => 'POST', 'data-confirm' => Yii::t('AdminModule.views_module_list', 'Are you sure? *ALL* module related data and files will be lost!'))); ?>
                        <?php endif; ?>

                        &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_list', 'More info'), Url::to(['/admin/module/info', 'moduleId' => $moduleId]), array('data-target' => '#globalModal')); ?>

                    </div>

                </div>
            </div>
            <hr/>
        <?php endforeach; ?>
    </div>
</div>