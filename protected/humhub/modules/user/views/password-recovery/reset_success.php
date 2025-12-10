<?php

use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password reset');
?>
<div id="user-password-recovery-reset-success" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default animated fadeIn">
        <div class="panel-heading">
            <?= Yii::t('UserModule.auth', '<strong>Password</strong> changed!') ?>
        </div>
        <div class="panel-body">
            <p><?= Yii::t('UserModule.auth', "Your password has been successfully changed!"); ?></p><br/>
            <a href="<?= Url::home() ?>" data-ui-loader data-pjax-prevent
               class="btn btn-primary"><?= Yii::t('UserModule.auth', 'Login') ?></a>
        </div>
    </div>
</div>
