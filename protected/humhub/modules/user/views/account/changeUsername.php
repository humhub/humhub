<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

$userModule = Yii::$app->getModule('user');
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?= Yii::t('UserModule.views_account_changeUsername', 'Your current username is <b>{username}</b>. You can change your current username here. <div class="alert alert-warning">Note: Changing your username will invalidate old profile links (not including mentionings)</div>', ['username' => Html::encode(Yii::$app->user->getIdentity()->username)]); ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?= $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => $userModule->maximumUsernameLength]); ?>

    <hr>
    <?= Html::submitButton(Yii::t('UserModule.views_account_changeUsername', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

    <!-- show flash message after saving -->
    <?= \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




