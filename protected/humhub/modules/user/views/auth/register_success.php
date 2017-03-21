<?php
$this->pageTitle = Yii::t('UserModule.views_auth_register_success', 'Registration successful');
?>
<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(['place' => 'login']); ?>
    <br>
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?= Yii::t('UserModule.views_auth_register_success', '<strong>Registration</strong> successful!'); ?></div>
            <div class="panel-body">
                <p><?= Yii::t('UserModule.views_auth_register_success', 'Please check your email and follow the instructions!'); ?></p>
                <br>
                <a href="<?= \yii\helpers\Url::to(["/"]) ?>" class="btn btn-primary"><?= Yii::t('UserModule.views_auth_register_success', 'back to home') ?></a>
            </div>
        </div>
    </div>
</div>



