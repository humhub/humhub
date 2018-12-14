<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;

$userModule = Yii::$app->getModule('user');
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
         <?php echo Yii::t('UserModule.views_account_changeUsername', 'Your current username is <b>{username}</b>. You can change your current username here. Changing the username can make some links unusable, for example old links to the profile', ['username' => CHtml::encode(Yii::$app->user->getIdentity()->username)]); ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isAttributeRequired('currentPassword')): ?>
        <?php echo $form->field($model, 'currentPassword')->passwordInput(['maxlength' => 45]); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'newUsername')->textInput(['maxlength' => $userModule->maximumUsernameLength]); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_changeUsername', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>




