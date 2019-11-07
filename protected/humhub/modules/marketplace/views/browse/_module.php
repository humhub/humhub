<?php

use humhub\modules\marketplace\models\Licence;
use humhub\modules\ui\view\components\View;
use yii\helpers\Url;
use yii\helpers\Html;

/* @var $licence Licence */
/* @var $module array */
/* @var $view View */

$isInstalled = (Yii::$app->moduleManager->hasModule($module['id']));

$moduleImageUrl = Yii::getAlias('@web-static/img/default_module.jpg');
if (isset($module['moduleImageUrl']) && $module['moduleImageUrl'] != "") {
    $moduleImageUrl = $module['moduleImageUrl'];
}


$isProFeature = (!empty($module['professional_only']));
$isProOnly = ($isProFeature && $licence->type !== Licence::LICENCE_TYPE_PRO);

?>

<hr>
<div class="media <?php if ($isInstalled || $isProOnly): ?>module-installed<?php endif; ?>">
    <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
         style="width: 64px; height: 64px;" src="<?= $moduleImageUrl; ?>">

    <div class="media-body">

        <h4 class="media-heading">
            <?= $module['name']; ?>

            <?php if (!empty($module['featured'])): ?>
                <i class="fa fa-star text-info" aria-hidden="true"></i>
                &nbsp;
            <?php endif; ?>

            <?php if ($isProFeature): ?>
                <a href="<?= Url::to(['/admin/information']) ?>">
                    <span class="badge badge-danger pull-right"
                          style="font-size:12px;line-height:18px;background-color:#3F4B5A;color:white">
                    <?php if ($isProOnly): ?><i class="fa fa-lock"></i><?php endif; ?>
                    Professional Edition Feature
            </span>
                </a>
            <?php endif; ?>

        </h4>

        <p><?= $module['description']; ?></p>

        <div class="module-controls">
            <?= Yii::t('MarketplaceModule.base', 'Latest version:'); ?> <?= $module['latestVersion']; ?>

            <?php if ($isInstalled): ?>
                &middot; <?= Yii::t('MarketplaceModule.base', 'Installed'); ?>
                </span>
            <?php endif; ?>

            <?php if (isset($module['purchased']) && $module['purchased']) : ?>
                &nbsp; Purchased
            <?php endif; ?>

            <?php if (isset($module['latestCompatibleVersion'])) : ?>

                <?php if ($module['latestCompatibleVersion'] != $module['latestVersion']) : ?>
                    &middot; <?= Yii::t('MarketplaceModule.base', 'Latest compatible version:'); ?>  <?= $module['latestCompatibleVersion']; ?>
                <?php endif; ?>

                <?php if (!$isInstalled): ?>
                    <?php if ($isProOnly): ?>
                        &middot; <?= Html::a('<i class="fa fa-lock"></i> Professional Edition', ['/admin/information'], ['style' => 'font-weight:bold']); ?>
                    <?php elseif (!empty($module['price_eur']) && !$module['purchased']) : ?>
                        <?php $checkoutUrl = str_replace('-returnToUrl-', Url::to(['/marketplace/purchase/list'], true), $module['checkoutUrl']); ?>
                        &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'Buy (%price%)', ['%price%' => $module['price_eur'] . '&euro;']), $checkoutUrl, ['style' => 'font-weight:bold', 'target' => '_blank']); ?>
                    <?php else: ?>
                        &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'Install'), Url::to(['/marketplace/browse/install', 'moduleId' => $module['id']]), ['style' => 'font-weight:bold', 'data-loader' => "modal", 'data-message' => Yii::t('MarketplaceModule.base', 'Installing module...'), 'data-method' => 'POST']); ?>
                    <?php endif; ?>

                <?php endif; ?>

            <?php else : ?>
                &middot; <span
                    style="color:red"><?= Yii::t('MarketplaceModule.base', 'No compatible module version found!'); ?></span>
            <?php endif; ?>

            &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'More info') .
                '&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i>'
                , $module['marketplaceUrl'], ['target' => '_blank']); ?>

            <?php if (!empty($module['showDisclaimer'])): ?>
                &middot; <?= Html::a(Yii::t('MarketplaceModule.base', 'Third-party'), Url::to(['thirdparty-disclaimer']), ['data-target' => '#globalModal']); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
