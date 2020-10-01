<?php

use humhub\modules\ui\icon\widgets\Icon;
use humhub\libs\Html;
use yii\helpers\Url;

/* @var $modules [] */

?>
<div class="panel-body">
    <?= Html::beginForm(Url::to(['/marketplace/update', 'betaSwitch' => 1]), 'post', ['id' => 'betaSwitchForm']); ?>
    <div class="form-group pull-right">
        <label>
            <?= Html::checkbox('includeBetaUpdates', $includeBetaUpdates, ['id' => 'chkBeta']); ?>
            <?= Yii::t('MarketplaceModule.base', 'Include beta updates'); ?>
        </label>
    </div>
    <?= Html::endForm(); ?>
    <br>
    <br>

    <?php if (empty($modules)): ?>
        <br>
        <div
            class="alert alert-success"><?= Icon::get('check') ?> <?= Yii::t('MarketplaceModule.base', 'All modules are up to date!'); ?></div>
    <?php endif; ?>

    <?php foreach ($modules as $module): ?>

        <?php
        $moduleImageUrl = Yii::getAlias('@web-static/img/default_module.jpg');
        if (!empty($module['moduleImageUrl'])) {
            $moduleImageUrl = $module['moduleImageUrl'];
        }
        ?>
        <div class="media">
            <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                 style="width: 64px; height: 64px;" src="<?= $moduleImageUrl; ?>">

            <div class="media-body">
                <h4 class="media-heading"><?= $module['name']; ?> </h4>

                <?php if (isset($module['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($module['id'])) : ?>
                    <?= Html::a('<i class="fa fa-download"> </i>&nbsp;&nbsp;' . Yii::t('MarketplaceModule.base', 'Update'), ['/marketplace/update/install', 'moduleId' => $module['id']], ['class' => 'pull-right btn btn-success btn-sm', 'data-loader' => "modal", 'data-message' => Yii::t('MarketplaceModule.base', 'Updating module...'), 'data-method' => 'POST']); ?>
                <?php endif; ?>
                <p><?= $module['description']; ?></p>


                <div class="module-controls">
                    <?php if (isset($module['latestCompatibleVersion']) && Yii::$app->moduleManager->hasModule($module['id'])) : ?>
                        <?= Yii::t('MarketplaceModule.base', 'Installed version:'); ?>&nbsp;<?= Yii::$app->moduleManager->getModule($module['id'])->getVersion(); ?>
                        &middot; <?= Yii::t('MarketplaceModule.base', 'Latest compatible Version:'); ?>&nbsp;<?= $module['latestCompatibleVersion']; ?>
                        &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'View Changelog'), $module['marketplaceUrl'] . '#changelog', ['target' => '_blank']); ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>
</div>

<script <?= Html::nonce(); ?>>
    $('#chkBeta').change(function () {
        $('#betaSwitchForm').submit();
    });
</script>
