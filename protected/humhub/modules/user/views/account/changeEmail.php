<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?php echo Yii::t('UserModule.views_account_changeEmail', 'Your current E-mail address is <b>{email}</b>. You can change your current E-mail address here.', ['email' => CHtml::encode(Yii::$app->user->getIdentity()->email)]); ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'newEmail')->textInput(['maxlength' => 45]); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changeEmail', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




