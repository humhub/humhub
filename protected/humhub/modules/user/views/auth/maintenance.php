<?php

use humhub\helpers\Html;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Maintenance mode');

/* @var $this \humhub\components\View */

$customInfo = trim((string)Yii::$app->settings->get('maintenanceModeInfo', ''));

?>

<div id="user-auth-maintenance" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated bounceIn">
        <div class="panel-heading">
            <?= Yii::t('UserModule.auth', 'Maintenance mode is active') ?>
        </div>
        <div class="panel-body">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if ($customInfo !== ''): ?>
                <p><?= Html::encode($customInfo) ?></p>
            <?php else: ?>
                <p><?= Yii::t('UserModule.auth', 'Only administrators can access the platform during maintenance.') ?></p>
            <?php endif; ?>

            <p class="text-center mt-3">
                <small>
                    <?= Html::a(
                        Yii::t('UserModule.auth', 'Admin login'),
                        Url::to(['/user/auth/login', 'maintenanceAdmin' => 1]),
                        [
                            'id' => 'maintenance-admin-login',
                            'class' => 'link-accent',
                            'data' => ['pjax-prevent' => true],
                        ],
                    ) ?>
                </small>
            </p>
        </div>
    </div>

    <br>

    <?= LanguageChooser::widget(['vertical' => true, 'hideLabel' => true]) ?>
</div>
