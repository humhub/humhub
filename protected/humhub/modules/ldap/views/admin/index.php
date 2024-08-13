<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @var $this \yii\web\View
 * @var $enabled boolean
 * @var $errorMessage string
 * @var $model \humhub\modules\ldap\models\LdapSettings
 * @var $userCount string
 */

use yii\helpers\Html;
use humhub\modules\ui\form\widgets\ActiveForm;

?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>

<div class="panel-body">

    <div class="help-block">
        <?= Yii::t('LdapModule.base', 'Specify your LDAP-backend used to fetch user accounts.') ?>
    </div>
    <br>
    <?php if ($enabled): ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= Yii::t('LdapModule.base', 'Status: Error! (Message: {message})', ['{message}' => $errorMessage]) ?>
            </div>
        <?php elseif ($userCount == 0): ?>
            <div class="alert alert-warning">
                <?= Yii::t('LdapModule.base', 'Status: Warning! (No users found using the ldap user filter!)') ?>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <?= Yii::t('LdapModule.base', 'Status: OK! ({userCount} Users)', ['{userCount}' => $userCount]) ?>
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
    <?= $form->field($model, 'passwordField')->passwordInput() ?>
    <?= $form->field($model, 'baseDn')->textInput() ?>
    <?= $form->field($model, 'loginFilter')->textArea() ?>
    <?= $form->field($model, 'userFilter')->textArea() ?>
    <?= $form->field($model, 'usernameAttribute')->textInput() ?>
    <?= $form->field($model, 'emailAttribute')->textInput() ?>
    <?= $form->field($model, 'idAttribute')->textInput() ?>
    <?= $form->field($model, 'refreshUsers')->checkbox() ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.base', 'Advanced settings')); ?>
    <?= $form->field($model, 'ignoredDNs')->textarea(['style' => 'white-space:nowrap;']) ?>
    <?= $form->endCollapsibleFields(); ?>

    <hr>

    <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

    <?php ActiveForm::end() ?>
</div>

<?php $this->endContent() ?>
