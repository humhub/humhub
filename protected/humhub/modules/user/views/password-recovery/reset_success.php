<?php

use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.views_auth_resetPassword', 'Password reset');
?>
<div class="container" style="text-align: center;">
    <?php echo humhub\widgets\SiteLogo::widget(array('place' => 'login')); ?>

    <br>
    <div class="row">
        <div class="panel panel-default animated fadeIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_resetPassword_success', '<strong>Password</strong> changed!'); ?></div>
            <div class="panel-body">
                <p><?= Yii::t('UserModule.views_auth_resetPassword_success', "Your password has been successfully changed!"); ?></p><br/>
                <a href="<?= Url::home() ?>" data-ui-loader data-pjax-prevent class="btn btn-primary"><?= Yii::t('UserModule.views_auth_resetPassword_success', 'Login') ?></a>
            </div>
        </div>
    </div>
</div>