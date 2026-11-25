<?php

use humhub\modules\user\components\MaintenanceModeGate;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Password changed!');

// During maintenance only the admin login form leads back into the platform
$loginRoute = MaintenanceModeGate::isActive() ? ['/user/auth/login', 'maintenanceAdmin' => 1] : ['/user/auth/login'];
?>
<div id="user-password-recovery-reset-success" class="container container-password">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="card content-card animated fadeIn">
        <div class="card-header">
            <h1 class="card-title"><strong><?= Yii::t('UserModule.auth', 'Password changed!') ?></strong></h1>
        </div>
        <div class="card-body">
            <p><?= Yii::t('UserModule.auth', 'Your password has been successfully changed!') ?></p>
            <br>
            <?= Button::primary(Yii::t('UserModule.auth', 'Sign In'))
                ->link($loginRoute)
                ->cssClass('w-100')
                ->pjax(false) ?>
        </div>
    </div>
</div>
