<?php
/**
 * @var $this \yii\web\View
 * @var $enabled boolean
 * @var $errorMessage string
 * @var $model \humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm
 * @var $userCount string
 */

use humhub\models\Setting;
use humhub\widgets\DataSaved;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<div class="panel-body">

    <div class="help-block">
        <?= Yii::t(
            'AdminModule.views_setting_authentication_ldap',
            'Specify your LDAP-backend used to fetch user accounts.'
        ) ?>
    </div>
    <br>
    <?php if ($enabled): ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= Yii::t(
                    'AdminModule.views_setting_authentication_ldap',
                    'Status: Error! (Message: {message})',
                    ['{message}' => $errorMessage]
                ) ?>
            </div>
        <?php elseif ($userCount == 0): ?>
            <div class="alert alert-warning">
                <?= Yii::t(
                    'AdminModule.views_setting_authentication_ldap',
                    'Status: Warning! (No users found using the ldap user filter!)'
                ) ?>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <?= Yii::t(
                    'AdminModule.views_setting_authentication_ldap',
                    'Status: OK! ({userCount} Users)',
                    ['{userCount}' => $userCount]
                ) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'authentication-settings-form',
        'fieldConfig' => function ($model, $attribute) {
            return [
                'inputOptions' => [
                    'class' => 'form-control',
                    'readonly' => Yii::$app->getModule('user')->settings->isFixed('auth.ldap.' . $attribute)
                ],
            ];
        }
    ]) ?>

    <?= $form->field($model, 'enabled')->checkbox() ?>
    <hr>

    <?= $form->field($model, 'hostname')->textInput() ?>
    <?= $form->field($model, 'port')->textInput() ?>
    <?= $form->field($model, 'encryption')->dropDownList($model->encryptionTypes) ?>
    <?= $form->field($model, 'username')->textInput() ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'baseDn')->textInput() ?>
    <?= $form->field($model, 'loginFilter')->textArea() ?>
    <?= $form->field($model, 'userFilter')->textArea() ?>
    <?= $form->field($model, 'usernameAttribute')->textInput() ?>
    <?= $form->field($model, 'emailAttribute')->textInput() ?>
    <?= $form->field($model, 'idAttribute')->textInput() ?>
    <?= $form->field($model, 'refreshUsers')->checkbox() ?>
    <hr>

    <?= Html::submitButton(
            Yii::t('AdminModule.views_setting_authentication_ldap', 'Save'),
            ['class' => 'btn btn-primary', 'data-ui-loader' => '']
    ) ?>

    <?= DataSaved::widget() ?>
    <?php ActiveForm::end() ?>
</div>
<?php $this->endContent() ?>