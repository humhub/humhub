<?php

use yii\helpers\Html;

?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_module_listUpdates', '<strong>Modules</strong> directory'); ?></div>
   <?php echo $this->render('_header'); ?>
    
    <div class="panel-body">
        <?php if (count($modules) == 0): ?>
            <br>
            <div><?php echo Yii::t('AdminModule.module_listUpdates', 'All modules are up to date!'); ?></div>

        <?php endif; ?>

        <?php foreach ($modules as $module): ?>
            
            <?php
            $moduleImageUrl = Yii::getAlias('@web/img/default_module.jpg');
            if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                $moduleImageUrl = $module['moduleImageUrl'];
            }
            ?>

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo $moduleImageUrl; ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $module['name']; ?> </h4>

                    <p><?php echo $module['description']; ?></p>

                    <div class="module-controls">

                        <?php if (isset($module['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($module['id'])) : ?>
                            <?php echo Yii::t('AdminModule.views_module_listUpdates', 'Installed version:'); ?><?php echo Yii::$app->moduleManager->getModule($module['id'])->getVersion(); ?>
                            &middot; <?php echo Yii::t('AdminModule.views_module_listUpdates', 'Latest compatible Version:'); ?><?php echo $module['latestCompatibleVersion']; ?>
                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listUpdates', 'Update'), ['update', 'moduleId' => $module['id']], array('style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_listUpdates', 'Updating module...'), 'data-method' => 'POST')); ?>
                            &middot; <?php echo Html::a(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], array('target' => '_blank')); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <hr/>
        <?php endforeach; ?>

    </div>
</div>