<?php

use yii\helpers\Html;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('AdminModule.views_module_listUpdates', '<strong>Modules</strong> directory'); ?></div>
    <?= $this->render('_header'); ?>

    <div class="panel-body">
        <?php if (count($modules) == 0): ?>
            <br>
            <div><?= Yii::t('AdminModule.module_listUpdates', 'All modules are up to date!'); ?></div>

        <?php endif; ?>

        <?php foreach ($modules as $module): ?>

            <?php
            $moduleImageUrl = Yii::getAlias('@web-static/img/default_module.jpg');
            if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
                $moduleImageUrl = $module['moduleImageUrl'];
            }
            ?>

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="<?= $moduleImageUrl; ?>">

                <div class="media-body">
                    <h4 class="media-heading"><?= $module['name']; ?> </h4>

                    <p><?= $module['description']; ?></p>

                    <div class="module-controls">

                        <?php if (isset($module['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($module['id'])) : ?>
                            <?= Yii::t('AdminModule.views_module_listUpdates', 'Installed version:'); ?><?= Yii::$app->moduleManager->getModule($module['id'])->getVersion(); ?>
                            &middot; <?= Yii::t('AdminModule.views_module_listUpdates', 'Latest compatible Version:'); ?><?= $module['latestCompatibleVersion']; ?>
                            &middot; <?= Html::a(Yii::t('AdminModule.views_module_listUpdates', 'Update'), ['update', 'moduleId' => $module['id']], ['style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('AdminModule.views_module_listUpdates', 'Updating module...'), 'data-method' => 'POST']); ?>
                            &middot; <?= Html::a(Yii::t('AdminModule.views_module_listOnline', 'More info'), $module['marketplaceUrl'], ['target' => '_blank']); ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <hr>
        <?php endforeach; ?>

    </div>
</div>