<?php
$this->pageTitle = Yii::t('UserModule.auth', 'Registration successful');
?>
<div class="container">
    <div class="row">
        <div class="panel panel-default panel-login">
            <div class="user-icon">
                <i class="fa fa-user-o" aria-hidden="true"></i>
            </div>
            <div class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Registration</strong> successful!'); ?></div>
            <div class="panel-body-register-successful">
                <p class="text-line"><?php echo Yii::t('UserModule.auth', 'Please check your email and follow the instructions!'); ?></p>
                <br />
                <a href="<?php echo \yii\helpers\Url::to(["/"]) ?>" data-pjax-prevent data-ui-loader class="btn btn-large btn-login"><?php echo Yii::t('UserModule.auth', 'Back to home') ?></a>
            </div>
        </div>
    </div>
</div>