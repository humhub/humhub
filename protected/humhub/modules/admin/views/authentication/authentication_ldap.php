<?php

/**
 * @var $this \yii\web\View
 * @var $enabled boolean
 * @var $errorMessage string
 * @var $model \humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm
 * @var $userCount string
 */
use humhub\models\Setting;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<div class="panel-body">

    <div class="help-block">
        <?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Specify your LDAP-backend used to fetch user accounts.') ?>
    </div>
    <br>
    <?php if ($enabled): ?>
        <?php if ($errorMessage != ""): ?>
            <div class="alert alert-danger"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Status: Error! (Message: {message})', ['{message}' => $errorMessage]); ?></div>
        <?php elseif ($userCount == 0): ?>
            <div class="alert alert-warning"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Status: Warning! (No users found using the ldap user filter!)'); ?></div>
        <?php else: ?>
            <div class="alert alert-success"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Status: OK! ({userCount} Users)', ['{userCount}' => $userCount]); ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form']); ?>

    <?= $form->field($model, 'enabled')->checkbox(['readonly' => Setting::IsFixed('auth.ldap.enabled', 'user')]); ?>
    <hr>

    <?= $form->field($model, 'hostname')->textInput(['readonly' => Setting::IsFixed('auth.ldap.hostname', 'user')]); ?>
    <?= $form->field($model, 'port')->textInput(['readonly' => Setting::IsFixed('auth.ldap.port', 'user')]); ?>
    <?= $form->field($model, 'encryption')->dropDownList($model->encryptionTypes, ['readonly' => Setting::IsFixed('auth.ldap.encryption', 'user')]); ?>
    <?= $form->field($model, 'username')->textInput(['readonly' => Setting::IsFixed('auth.ldap.username', 'user')]); ?>
    <?= $form->field($model, 'password')->passwordInput(['readonly' => Setting::IsFixed('auth.ldap.password', 'user')]); ?>
    <?= $form->field($model, 'baseDn')->textInput(['readonly' => Setting::IsFixed('auth.ldap.baseDn', 'user')]); ?>
    <?= $form->field($model, 'loginFilter')->textArea(['readonly' => Setting::IsFixed('auth.ldap.loginFilter', 'user')]); ?>
    <?= $form->field($model, 'userFilter')->textArea(['readonly' => Setting::IsFixed('auth.ldap.userFilter', 'user')]); ?>
    <?= $form->field($model, 'usernameAttribute')->textInput(['readonly' => Setting::IsFixed('auth.ldap.usernameAttribute', 'user')]); ?>
    <?= $form->field($model, 'emailAttribute')->textInput(['readonly' => Setting::IsFixed('auth.ldap.emailAttribute', 'user')]); ?>
    <?= $form->field($model, 'idAttribute')->textInput(['readonly' => Setting::IsFixed('auth.ldap.idAttribute', 'user')]); ?>
    <?= $form->field($model, 'refreshUsers')->checkbox(['readonly' => Setting::IsFixed('auth.ldap.refreshUsers', 'user')]); ?>

    <hr>
    <?= Html::submitButton(Yii::t('AdminModule.views_setting_authentication_ldap', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>