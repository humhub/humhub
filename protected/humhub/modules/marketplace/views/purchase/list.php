<?php

use humhub\components\Module;
use humhub\helpers\Html;
use humhub\modules\marketplace\assets\Assets;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\modal\Modal;
use yii\base\View;
use yii\helpers\Url;

/* @var Module[] $modules */
/* @var string $licenceKey */
/* @var bool $hasError */
/* @var string $message */
/* @var View $this */

Assets::register($this);
?>

<?php Modal::beginDialog([
    'title' => Yii::t('MarketplaceModule.base', 'Add License Key'),
]) ?>

    <!-- search form -->
    <?= Html::beginForm(Url::to(['/marketplace/purchase']), 'post', ['class' => 'form-search']); ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="mb-3 form-group-search">
                <?= Html::textInput('licenceKey', $licenceKey, ['class' => 'form-control form-search', 'placeholder' => Yii::t('MarketplaceModule.base', 'Add purchased module by license key')]); ?>
                <?= Button::light(Yii::t('MarketplaceModule.base', 'Register'))
                    ->submit()
                    ->action('marketplace.registerLicenceKey')
                    ->cssClass('btn btn-light btn-sm form-button-search'); ?>
            </div>
            <?php if ($message != ''): ?>
                <div style="color:<?= ($hasError) ? 'red' : 'green'; ?>"><?= Html::encode($message); ?></div>
            <?php endif; ?>
        </div>
        <div class="col-lg-3"></div>
    </div>
    <?= Html::endForm(); ?>

    <br/>

    <?php if (empty($modules)) : ?>
        <div class="text-center">
            <em><?= Yii::t('MarketplaceModule.base', 'No purchased modules found!'); ?></em>
            <br><br>
        </div>
    <?php else: ?>

        <?php foreach ($modules as $module): ?>
            <hr>
            <div class="d-flex">
                <div class="flex-shrink-0 me-2">
                    <img class="rounded me-3" data-src="holder.js/64x64" alt="64x64"
                         style="width: 64px; height: 64px;"
                         src="<?= empty($module['moduleImageUrl']) ? Yii::getAlias('@web-static/img/default_module.jpg') : $module['moduleImageUrl'] ?>">
                </div>

                <div class="flex-grow-1">
                    <h5 class="mt-0">
                        <strong>
                            <?= $module['name'] ?>
                            <?php if (Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                <small><?= Badge::info(Yii::t('MarketplaceModule.base', 'Installed')) ?></small>
                            <?php endif; ?>
                        </strong>
                    </h5>
                    <p><?= $module['description']; ?></p>

                    <div class="module-controls">
                        <?php if (!Yii::$app->moduleManager->hasModule($module['id'])): ?>
                            <strong><?= Link::to(Yii::t('MarketplaceModule.base', 'Install'))
                                    ->action('marketplace.install', ['/marketplace/browse/install'])
                                    ->options(['data-module-id' => $module['id']]) ?></strong>
                            &middot;
                        <?php endif; ?>
                        <?= Html::a(Yii::t('MarketplaceModule.base', 'More info'), $module['marketplaceUrl'], ['target' => '_blank']); ?>
                        &middot; <?= Yii::t('MarketplaceModule.base', 'Latest version:'); ?> <?= $module['latestVersion']; ?>
                        &middot; <?= Yii::t('MarketplaceModule.base', 'License Key:'); ?> <?= $module['licence_key']; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="clearfix">
        <small class="float-end">
            <br/>Installation Id: <?= Yii::$app->getModule('admin')->settings->get('installationId') ?>
        </small>
    </div>

<?php Modal::endDialog() ?>
