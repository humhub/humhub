<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?php echo Yii::t('UserModule.account', 'Your current username is <b>{username}</b>. You can change your current username here.', ['username' => Html::encode(Yii::$app->user->getIdentity()->username)]); ?>
    </div>
    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'newUsername')->textInput(['maxlength' => 45]); ?>

    <hr>
    <?php echo Html::submitButton(Yii::t('UserModule.account', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




