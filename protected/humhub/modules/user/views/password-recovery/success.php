<?php

use humhub\widgets\bootstrap\Button;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password recovery');
?>
<div id="user-password-recovery-success" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated fadeIn">
        <div class="panel-heading">
            <?= Yii::t('UserModule.auth', '<strong>Password</strong> recovery!') ?>
        </div>
        <div class="panel-body">
            <p><?= Yii::t('UserModule.auth', 'If a user account associated with this email address exists, further instructions will be sent to you by email shortly.') ?></p>
            <br/>
            <?= Button::light(Yii::t('UserModule.auth', 'Back'))->link(Url::home())->pjax(false) ?>
        </div>
    </div>
</div>
