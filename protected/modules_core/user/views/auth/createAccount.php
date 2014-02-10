<?php
/**
 * Create account page, after the user clicked the email validation link.
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
        <div class="panel panel-default" style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.base', 'Registration'); ?></div>
            <div class="panel-body">
                <?php echo $form; ?>
            </div>
        </div>
    </div>
</div>
