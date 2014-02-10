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
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div
                class="panel-heading"><?php echo Yii::t('UserModule.base', 'Your account has been successfully created!'); ?></div>
            <div class="panel-body">
                <?php if ($needApproval) : ?>
                    <p><?php echo Yii::t('UserModule.auth', 'After activating your account by the administrator, you will receive a notification by email.'); ?></p>
                <?php else: ?>
                    <a href="<?php echo $this->createUrl('//') ?>"
                       class="btn btn-primary"><?php echo Yii::t('UserModule.auth', 'Got to login page') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



