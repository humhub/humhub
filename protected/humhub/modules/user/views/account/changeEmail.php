<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?php echo Yii::t('UserModule.account', 'Your current E-mail address is <b>{email}</b>. You can change your current E-mail address here.', ['email' => Html::encode(Yii::$app->user->getIdentity()->email)]); ?>
    </div>
    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'newEmail')->textInput(['maxlength' => 150]); ?>

    <hr>
    <?php echo Html::submitButton(Yii::t('UserModule.account', 'Save'), ['name' => 'save', 'class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




