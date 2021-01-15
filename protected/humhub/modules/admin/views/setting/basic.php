<?php

use humhub\libs\TimezoneHelper;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use yii\widgets\ActiveForm;
use humhub\compat\CHtml;

/* @var BasicSettingsForm $model */

$this->registerJsConfig('admin', $adminSettingsJsConfig = ['text' => [
    'maintenanceMode.header' => Yii::t('AdminModule.settings', '<strong>Maintenance</strong> mode'),
    'maintenanceMode.question.enable' => '<div class="alert alert-danger">'
        . Yii::t('AdminModule.settings', '<strong>WARNING:</strong> All non admin users will be logged out automatically after you save the settings form with enabled maintenance mode!')
        . '</div>'
        . Yii::t('AdminModule.settings', 'Do you really want to enable maintenance mode?'),
    'maintenanceMode.button.enable' => Yii::t('AdminModule.settings', 'Enable'),
    'maintenanceMode.question.disable' => Yii::t('AdminModule.settings', 'Are you sure all works have been done and the maintenance mode can be disable?'),
    'maintenanceMode.button.disable' => Yii::t('AdminModule.settings', 'Disable'),
]]);

AdminAsset::register($this);
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'General Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.settings', 'Here you can configure basic settings of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name'); ?>

    <?= $form->field($model, 'baseUrl'); ?>
    <p class="help-block"><?= Yii::t('AdminModule.settings', 'E.g. http://example.com/humhub'); ?></p>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?= $languageDropDown = $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages, ['data-ui-select2' => '']); ?>
    <?php endif; ?>

    <?= $form->field($model, 'timeZone')->dropDownList(TimezoneHelper::generateList(true), ['data-ui-select2' => '', 'disabled' => Yii::$app->settings->isFixed('timeZone')]); ?>
    <?= $form->field($model, 'defaultStreamSort')->dropDownList($model->getDefaultStreamSortOptions()); ?>

    <strong><?= Yii::t('AdminModule.settings', 'Dashboard'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'tour')->checkbox(); ?>
    <?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>

    <strong><?= Yii::t('AdminModule.settings', 'Friendship'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

    <strong><?= Yii::t('AdminModule.settings', 'Maintenance mode'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'maintenanceMode')->checkbox([
        'data-action-click' => 'admin.changeMaintenanceMode',
        'data-action-confirm-header' => $adminSettingsJsConfig['text']['maintenanceMode.header'],
        'data-action-confirm' => $adminSettingsJsConfig['text']['maintenanceMode.question.' . ($model->maintenanceMode ? 'disable' : 'enable')],
        'data-action-confirm-text' => $adminSettingsJsConfig['text']['maintenanceMode.button.' . ($model->maintenanceMode ? 'disable' : 'enable')],
    ]); ?>

    <hr>

    <?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <!-- show flash message after saving -->
    <?php \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>
