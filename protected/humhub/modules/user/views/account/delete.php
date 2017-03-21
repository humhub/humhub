<?php

use yii\bootstrap\Html;
use humhub\widgets\DataSaved;
use humhub\widgets\ActiveForm;
?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<?php if ($isSpaceOwner) : ?>
    <?= Yii::t('UserModule.views_account_delete', 'Sorry, as an owner of a workspace you are not able to delete your account!<br />Please assign another owner or delete them.'); ?>
<?php else: ?>
    <?= Yii::t('UserModule.views_account_delete', 'Are you sure, that you want to delete your account?<br />All your published content will be removed! '); ?>
    <br>
    <br>

    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45, 'placeholder' => Yii::t('UserModule.views_account_delete', 'Enter your password to continue')])->label(false); ?>
    <?php else: ?>
        <?= $form->field($model, 'currentPassword')->hiddenInput()->label(false); ?>
    <?php endif; ?>

    <?= Html::submitButton(Yii::t('UserModule.views_account_delete', 'Delete account'), array('class' => 'btn btn-danger', 'data-ui-loader' => '')); ?>
    <?= DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>

<?php endif; ?>
<?php $this->endContent(); ?>