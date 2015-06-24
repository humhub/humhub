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
    <?php $this->widget('application.widgets.LogoWidget', array('place' => 'login')); ?>
    <br>
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_register_success', '<strong>Registration</strong> successful!'); ?></div>
            <div class="panel-body">
                <p><?php echo Yii::t('UserModule.views_auth_register_success', 'Please check your email and follow the instructions!'); ?></p>
                <br/>
                <a href="<?php echo $this->createUrl('//') ?>" class="btn btn-primary"><?php echo Yii::t('UserModule.views_auth_register_success', 'back to home') ?></a>
            </div>
        </div>
    </div>
</div>



