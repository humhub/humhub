<?php

use yii\helpers\Url;
use humhub\widgets\Button;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Password recovery');
?>
<div class="container" style="text-align: center;">
    <?= SiteLogo::widget(['place' => 'login']); ?>
    <br>
    <div class="row">
        <div class="panel panel-default animated fadeIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Password</strong> recovery!'); ?></div>
            <div class="panel-body">
                <p><?= Yii::t('UserModule.auth', "We’ve sent you an email containing a link that will allow you to reset your password."); ?></p><br/>
                <?= Button::primary(Yii::t('UserModule.auth', 'back to home'))->link(Url::home())->pjax(false) ?>
            </div>
        </div>
    </div>
</div>