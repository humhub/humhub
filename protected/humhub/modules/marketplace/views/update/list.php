<?php

use yii\helpers\Html;

$moduleImageUrl = Yii::getAlias('@web-static/img/default_module.jpg');
if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
    $moduleImageUrl = $module['moduleImageUrl'];
}

?>
<div class="panel-body">
    <?php if (count($modules) == 0): ?>
        <br>
        <div><?= Yii::t('MarketplaceModule.base', 'All modules are up to date!'); ?></div>

    <?php endif; ?>

    <?php foreach ($modules as $module): ?>

        <div class="media">
            <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                 style="width: 64px; height: 64px;" src="<?= $moduleImageUrl; ?>">

            <div class="media-body">
                <h4 class="media-heading"><?= $module['name']; ?> </h4>

                <p><?= $module['description']; ?></p>

                <div class="module-controls">

                    <?php if (isset($module['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($module['id'])) : ?>
                        <?= Yii::t('MarketplaceModule.base', 'Installed version:'); ?><?= Yii::$app->moduleManager->getModule($module['id'])->getVersion(); ?>
                        &middot; <?= Yii::t('MarketplaceModule.base', 'Latest compatible Version:'); ?><?= $module['latestCompatibleVersion']; ?>
                        &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'Update'), ['/marketplace/update/install', 'moduleId' => $module['id']], ['style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('MarketplaceModule.base', 'Updating module...'), 'data-method' => 'POST']); ?>
                        &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'More info'), $module['marketplaceUrl'], ['target' => '_blank']); ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>
</div>
