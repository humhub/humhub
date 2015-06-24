<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', '<strong>Authentication</strong> - LDAP'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li>
                <a href="<?php echo $this->createUrl('authentication'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Basic'); ?></a>
            </li>
            <li class="active"><a
                    href="<?php echo $this->createUrl('authenticationLdap'); ?>"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP'); ?></a>
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

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'authentication-settings-form',
            'id' => 'file-settings-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'enabled', array('readonly' => HSetting::IsFixed('enabled', 'authentication_ldap'))); ?> <?php echo $model->getAttributeLabel('enabled'); ?>
                </label>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'hostname'); ?>
            <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('hostname', 'authentication_ldap'))); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'port'); ?>
            <?php echo $form->textField($model, 'port', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('port', 'authentication_ldap'))); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'encryption'); ?>
            <?php echo $form->dropDownList($model, 'encryption', $model->encryptionTypes, array('class' => 'form-control', 'readonly' => HSetting::IsFixed('encryption', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.'); ?></p>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'username'); ?>
            <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('username', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.'); ?></p>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('password', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default credentials password (used only with username above).'); ?></p>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'baseDn'); ?>
            <?php echo $form->textField($model, 'baseDn', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('baseDn', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'The default base DN used for searching for accounts.'); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'loginFilter'); ?>
            <?php echo $form->textField($model, 'loginFilter', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('loginFilter', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;'); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'userFilter'); ?>
            <?php echo $form->textField($model, 'userFilter', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('userFilter', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'usernameAttribute'); ?>
            <?php echo $form->textField($model, 'usernameAttribute', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('usernameAttribute', 'authentication_ldap'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_authentication_ldap', 'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;'); ?></p>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'refreshUsers', array('readonly' => HSetting::IsFixed('refreshUsers', 'authentication_ldap'))); ?> <?php echo $model->getAttributeLabel('refreshUsers'); ?>
                </label>
            </div>
        </div>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_authentication_ldap', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>


    </div>
</div>


