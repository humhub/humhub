<?php

use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password reset');
?>
<div class="container" style="text-align: center;">
    <?php echo humhub\widgets\SiteLogo::widget(['place' => 'login']); ?>

    <br>
    <div class="row">
        <div class="card card-default animated fadeIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="card-header">
                <?php echo Yii::t('UserModule.auth', '<strong>Password</strong> changed!'); ?>
            </div>

            <div class="card-body">
                <p><?= Yii::t('UserModule.auth', "Your password has been successfully changed!"); ?></p><br/>
                <a href="<?= Url::home() ?>" data-ui-loader data-pjax-prevent class="btn btn-primary"><?= Yii::t('UserModule.auth', 'Login') ?></a>
            </div>
        </div>
    </div>
</div>
