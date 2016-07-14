<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', '<strong>Authentication</strong> - LDAP'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li>
                <a href="<?php echo Url::toRoute('authentication'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Basic'); ?></a>
            </li>
            <li class="active"><a
                    href="<?php echo Url::toRoute('authentication-ldap'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP'); ?></a>
            </li>
        </ul>

        <br/>

        <?php if ($enabled): ?>
            <?php if ($errorMessage != ""): ?>
                <div
                    class="danger"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Status: Error! (Message: {message})', array('{message}' => $errorMessage)); ?></div>
                <?php else: ?>
                <div
                    class="success"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Status: OK! ({userCount} Users)', array('{userCount}' => $userCount)); ?></div>
                <?php endif; ?>
            <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'authentication-settings-form']); ?>


        <?php echo $form->field($model, 'enabled')->checkbox(['readonly' => Setting::IsFixed('enabled', 'authentication_ldap')]); ?>
        <hr>

        <?php echo $form->field($model, 'hostname')->textInput(['readonly' => Setting::IsFixed('hostname', 'authentication_ldap')]); ?>

        <?php echo $form->field($model, 'port')->textInput(['readonly' => Setting::IsFixed('port', 'authentication_ldap')]); ?>

        <?php echo $form->field($model, 'encryption')->dropDownList($model->encryptionTypes, ['readonly' => Setting::IsFixed('encryption', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.'); ?></p>

        <?php echo $form->field($model, 'username')->textInput(['readonly' => Setting::IsFixed('username', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.'); ?></p>

        <?php echo $form->field($model, 'password')->passwordInput(['readonly' => Setting::IsFixed('password', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials password (used only with username above).'); ?></p>

        <?php echo $form->field($model, 'baseDn')->textInput(['readonly' => Setting::IsFixed('baseDn', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default base DN used for searching for accounts.'); ?></p>

        <?php echo $form->field($model, 'loginFilter')->textInput(['readonly' => Setting::IsFixed('loginFilter', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;'); ?></p>

        <?php echo $form->field($model, 'userFilter')->textInput(['readonly' => Setting::IsFixed('userFilter', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'); ?></p>

        <?php echo $form->field($model, 'usernameAttribute')->textInput(['readonly' => Setting::IsFixed('usernameAttribute', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;'); ?></p>

        <?php echo $form->field($model, 'emailAttribute')->textInput(['readonly' => Setting::IsFixed('emailAttribute', 'authentication_ldap')]); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;'); ?></p>


        <?php echo $form->field($model, 'refreshUsers')->checkbox(['readonly' => Setting::IsFixed('refreshUsers', 'authentication_ldap')]); ?>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_authentication_ldap', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>


