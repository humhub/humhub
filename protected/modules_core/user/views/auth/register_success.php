<?php
/**
 * After E-Mail was provided for registration, this view is shown.
 * This indicates an approval e-mail was sent to the given address.
 *
 * @property CFormModel $registerModel is the registration form.
 *
 * @package humhub.modules_core.user.views
 * @since 0.5
 *
 * @var $this AuthController
 */
?>
<div class="container" style="text-align: center;">
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.base', 'Registration successful!'); ?></div>
            <div class="panel-body">
                <p><?php echo Yii::t('UserModule.base', 'Please check your email and follow the instructions!'); ?></p>
                <a href="<?php echo $this->createUrl('//') ?>" class="btn btn-primary"><?php echo Yii::t('UserModule.auth', 'back to home') ?></a>
            </div>
        </div>
    </div>
</div>



