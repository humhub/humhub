<?php

use humhub\components\Module;
use humhub\modules\marketplace\assets\Assets;
use humhub\widgets\Button;
use yii\base\View;
use yii\helpers\Url;
use yii\helpers\Html;

/* @var Module[] $modules */
/* @var string $licenceKey */
/* @var bool $hasError */
/* @var string $message */
/* @var View $this */

Assets::register($this);
?>
<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('MarketplaceModule.base', 'Add Licence Key'); ?>
            </h4>
        </div>
        <div class="modal-body">

            <!-- search form -->
            <?= Html::beginForm(Url::to(['/marketplace/purchase']), 'post', ['class' => 'form-search']); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-group-search">
                        <?= Html::textInput('licenceKey', $licenceKey, ['class' => 'form-control form-search', 'placeholder' => Yii::t('MarketplaceModule.base', 'Add purchased module by licence key')]); ?>
                        <?= Button::defaultType(Yii::t('MarketplaceModule.base', 'Register'))
                            ->submit()
                            ->action('marketplace.registerLicenceKey')
                            ->cssClass('btn btn-default btn-sm form-button-search'); ?>
                    </div>
                    <?php if ($message != ''): ?>
                        <div style="color:<?= ($hasError) ? 'red' : 'green'; ?>"><?= Html::encode($message); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3"></div>
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
                    <div class="media">
                        <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                             style="width: 64px; height: 64px;"
                             src="<?= empty($module['moduleImageUrl']) ? Yii::getAlias('@web-static/img/default_module.jpg') : $module['moduleImageUrl']; ?>">

                        <div class="media-body">
                            <h4 class="media-heading"><?= $module['name']; ?>
                                <?php if (Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                    <small><span
                                            class="label label-info"><?= Yii::t('MarketplaceModule.base', 'Installed'); ?>
                                    </span></small>
                                <?php endif; ?>
                            </h4>
                            <p><?= $module['description']; ?></p>

                            <div class="module-controls">
                                <?php if (!Yii::$app->moduleManager->hasModule($module['id'])): ?>
                                    <?= Html::a(Yii::t('MarketplaceModule.base', 'Install'), Url::to(['/marketplace/browse/install', 'moduleId' => $module['id']]), ['style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('MarketplaceModule.base', 'Installing module...'), 'data-method' => 'POST']); ?>
                                    &middot;
                                <?php endif; ?>
                                <?= Html::a(Yii::t('MarketplaceModule.base', 'More info'), $module['marketplaceUrl'], ['target' => '_blank']); ?>
                                &middot; <?= Yii::t('MarketplaceModule.base', 'Latest version:'); ?> <?= $module['latestVersion']; ?>
                                &middot; <?= Yii::t('MarketplaceModule.base', 'Licence Key:'); ?> <?= $module['licence_key']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <small class="pull-right"><br />Installation Id: <?= Yii::$app->getModule('admin')->settings->get('installationId'); ?></small>
            <div class="clearfix"></div>

        </div>
    </div>
</div>
