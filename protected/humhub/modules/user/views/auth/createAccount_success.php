<?php
/**
 * Create Account Success page
 *
 * @property CFormModel $model is the create account form.
 * @property Boolean $needApproval indicates that new users requires admin approval.
 *
 * @package humhub.modules_core.user.views
 * @since 0.5
 */
?>

<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo CHtml::encode(Yii::app()->name); ?></h1>
    <br/>
    <div class="row">
        <div class="panel panel-default animated fadeIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div
                class="panel-heading"><?php echo Yii::t('UserModule.views_auth_createAccount_success', '<strong>Your account</strong> has been successfully created!'); ?></div>
            <div class="panel-body">
                <?php if ($needApproval) : ?>
                    <p><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'After activating your account by the administrator, you will receive a notification by email.'); ?></p>
                    <br/>
                    <a href="<?php echo $this->createUrl('//') ?>" class="btn btn-primary"><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'back to home') ?></a>
                <?php else: ?>
                    <p><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'To log in with your new account, click the button below.'); ?></p>
                    <br/>
                    <a href="<?php echo $this->createUrl('//') ?>"
                       class="btn btn-primary"><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'Go to login page') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



