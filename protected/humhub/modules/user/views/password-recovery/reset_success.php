<?php

use humhub\widgets\bootstrap\Button;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Password changed!');
?>
<div id="user-password-recovery-reset-success" class="container container-password">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated fadeIn">
        <div class="panel-heading">
            <strong class="fw-bolder"><?= Yii::t('UserModule.auth', 'Password changed!') ?></strong>
        </div>
        <div class="panel-body">
            <p><?= Yii::t('UserModule.auth', 'Your password has been successfully changed!') ?></p>
            <br>
            <?= Button::primary(Yii::t('UserModule.auth', 'Sign In'))
                ->link(['/user/auth/login'])
                ->cssClass('w-100')
                ->pjax(false) ?>
        </div>
    </div>
</div>
