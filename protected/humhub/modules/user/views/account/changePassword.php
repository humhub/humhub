<?php

use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php'); ?>
<div class="text-body-secondary">
    <?php echo Yii::t('UserModule.account', 'Your current password can be changed here.') ?>
</div>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if ($model->isAttributeSafe('currentPassword')): ?>
    <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <hr>
<?php endif; ?>

<?php echo $form->field($model, 'newPassword')->passwordInput(['maxlength' => 45]); ?>

<?php echo $form->field($model, 'newPasswordConfirm')->passwordInput(['maxlength' => 45]); ?>

<hr>
<?php echo Html::submitButton(Yii::t('UserModule.account', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
