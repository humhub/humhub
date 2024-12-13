<?php

use humhub\libs\TimezoneHelper;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

/* @var BasicSettingsForm $model */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'General Settings') ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.settings', 'Here you can configure basic settings of your social network.') ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'baseUrl')->textInput(['disabled' => Yii::$app->settings->isFixed('baseUrl')]) ?>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?= $languageDropDown = $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages) ?>
    <?php endif; ?>
    <?= $form->field($model, 'defaultTimeZone')->dropDownList(TimezoneHelper::generateList(true), ['disabled' => Yii::$app->settings->isFixed('defaultTimeZone')]) ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Dashboard')) ?>
    <?= $form->field($model, 'tour')->checkbox() ?>
    <?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox() ?>
    <?= $form->endCollapsibleFields() ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Friendship')) ?>
    <?= $form->field($model, 'enableFriendshipModule')->checkbox() ?>
    <?= $form->endCollapsibleFields() ?>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Maintenance mode'), !$model->maintenanceMode) ?>
    <?= $form->field($model, 'maintenanceMode')->checkbox() ?>
    <?= $form->field($model, 'maintenanceModeInfo')->textInput(['placeholder' => Yii::t('AdminModule.settings', 'Add individual info text...')]) ?>
    <?= $form->endCollapsibleFields() ?>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]) ?>

    <?php ActiveForm::end(); ?>
</div>
