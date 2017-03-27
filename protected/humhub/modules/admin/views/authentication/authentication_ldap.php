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
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.'); ?></p>

    <?= $form->field($model, 'username')->textInput(['readonly' => Setting::IsFixed('auth.ldap.username', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.'); ?></p>

    <?= $form->field($model, 'password')->passwordInput(['readonly' => Setting::IsFixed('auth.ldap.password', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials password (used only with username above).'); ?></p>

    <?= $form->field($model, 'baseDn')->textInput(['readonly' => Setting::IsFixed('auth.ldap.baseDn', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'The default base DN used for searching for accounts.'); ?></p>

    <?= $form->field($model, 'loginFilter')->textInput(['readonly' => Setting::IsFixed('auth.ldap.loginFilter', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;'); ?></p>

    <?= $form->field($model, 'userFilter')->textInput(['readonly' => Setting::IsFixed('auth.ldap.userFilter', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'); ?></p>

    <?= $form->field($model, 'usernameAttribute')->textInput(['readonly' => Setting::IsFixed('auth.ldap.usernameAttribute', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;'); ?></p>

    <?= $form->field($model, 'emailAttribute')->textInput(['readonly' => Setting::IsFixed('auth.ldap.emailAttribute', 'user')]); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;'); ?></p>

    <?= $form->field($model, 'refreshUsers')->checkbox(['readonly' => Setting::IsFixed('auth.ldap.refreshUsers', 'user')]); ?>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.views_setting_authentication_ldap', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>