<?php

use humhub\libs\TimezoneHelper;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

/* @var BasicSettingsForm $model */

$this->registerJsConfig('admin', $adminSettingsJsConfig = ['text' => [
    'maintenanceMode.header' => Yii::t('AdminModule.settings', '<strong>Maintenance</strong> Mode'),
    'maintenanceMode.question.enable' => Yii::t('AdminModule.settings',
            'Activate maintenance mode and disable access to the platform for non-admin users?<br><br>') .
        '<div class="alert alert-danger">' .
        Yii::t('AdminModule.settings', '<strong>Warning:</strong> All users will be immediately logged out, except admins.') .
        '</div>',
    'maintenanceMode.button.enable' => Yii::t('AdminModule.settings', 'Activate'),
    'maintenanceMode.question.disable' => Yii::t('AdminModule.settings', 'Deactivate maintenance mode and enable all users to access the platform again?'),
    'maintenanceMode.button.disable' => Yii::t('AdminModule.settings', 'Deactivate'),
]]);

AdminAsset::register($this);
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'General Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.settings', 'Here you can configure basic settings of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'baseUrl'); ?>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?= $languageDropDown = $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages, ['data-ui-select2' => '']); ?>
    <?php endif; ?>
    <?= $form->field($model, 'defaultTimeZone')->dropDownList(TimezoneHelper::generateList(true), ['data-ui-select2' => '', 'disabled' => Yii::$app->settings->isFixed('defaultTimeZone')]); ?>
    <?= $form->field($model, 'timeZone')->dropDownList(TimezoneHelper::generateList(true), ['data-ui-select2' => '', 'disabled' => Yii::$app->settings->isFixed('timeZone')]); ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Dashboard')); ?>
    <?= $form->field($model, 'tour')->checkbox(); ?>
    <?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>
    <?= $form->endCollapsibleFields(); ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Friendship')); ?>
    <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>
    <?= $form->endCollapsibleFields(); ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Maintenance mode'), !$model->maintenanceMode); ?>
    <?= $form->field($model, 'maintenanceMode')->checkbox([
        'data-action-click' => 'admin.changeMaintenanceMode',
        'data-action-confirm-header' => $adminSettingsJsConfig['text']['maintenanceMode.header'],
        'data-action-confirm' => $adminSettingsJsConfig['text']['maintenanceMode.question.' . ($model->maintenanceMode ? 'disable' : 'enable')],
        'data-action-confirm-text' => $adminSettingsJsConfig['text']['maintenanceMode.button.' . ($model->maintenanceMode ? 'disable' : 'enable')],
    ]); ?>
    <?= $form->field($model, 'maintenanceModeInfo')->label(false)->textInput(['disabled' => !$model->maintenanceMode]); ?>
    <?= $form->endCollapsibleFields(); ?>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <!-- show flash message after saving -->
    <?php \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>
