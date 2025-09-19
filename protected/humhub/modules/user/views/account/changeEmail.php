<?php

use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<div class="text-body-secondary">
    <?php echo Yii::t('UserModule.account', 'Your current E-mail address is <b>{email}</b>. You can change your current E-mail address here.', ['email' => Html::encode(Yii::$app->user->getIdentity()->email)]); ?>
</div>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if ($model->isAttributeRequired('currentPassword')): ?>
    <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
<?php endif; ?>

<?php echo $form->field($model, 'newEmail')->textInput(['maxlength' => 150]); ?>

<hr>
<?php echo Html::submitButton(Yii::t('UserModule.account', 'Save'), ['name' => 'save', 'class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
