<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?php echo Yii::t('UserModule.account', 'Your current username is <b>{username}</b>. You can change your current username here.', ['username' => CHtml::encode(Yii::$app->user->getIdentity()->username)]); ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'newUsername')->textInput(['maxlength' => 45]); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.account', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




