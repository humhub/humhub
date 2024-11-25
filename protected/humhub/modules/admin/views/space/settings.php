<?php

use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminSpaceAsset;
use humhub\modules\admin\models\forms\SpaceSettingsForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\modules\space\widgets\SpacePickerField;

/* @var $model SpaceSettingsForm */
/* @var $joinPolicyOptions array */
/* @var $visibilityOptions array */
/* @var $contentVisibilityOptions array */
/* @var $indexModuleSelection array */

AdminSpaceAsset::register($this);

$this->registerJsConfig('admin.space', [
    'text' => [
        'confirm.header' => Yii::t('AdminModule.space', 'Convert Space Topics'),
        'confirm.body' => Yii::t('AdminModule.space', 'All existing Space Topics will be converted to Global Topics.'),
        'confirm.confirmText' => Yii::t('AdminModule.space', 'Convert'),
    ],
]);
?>

<h4><?= Yii::t('AdminModule.space', 'Space Settings'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.space', 'Here you can define your default settings for new spaces. These settings can be overwritten for each individual space.'); ?>
</div>

<?php $form = ActiveForm::begin(['id' => 'space-settings-form', 'acknowledge' => true]); ?>

<?= SpacePickerField::widget([
    'form' => $form,
    'model' => $model,
    'attribute' => 'defaultSpaceGuid',
    'selection' => $model->defaultSpaces,
]) ?>

<?= $form->field($model, 'defaultVisibility')->dropDownList($visibilityOptions) ?>

<?= $form->field($model, 'defaultJoinPolicy')->dropDownList($joinPolicyOptions, ['disabled' => $model->defaultVisibility == 0]) ?>

<?= $form->field($model, 'defaultContentVisibility')->dropDownList($contentVisibilityOptions, ['disabled' => $model->defaultVisibility == 0]) ?>

<?= $form->field($model, 'defaultIndexRoute')->dropDownList($indexModuleSelection) ?>

<?= $form->field($model, 'defaultIndexGuestRoute')->dropDownList($indexModuleSelection) ?>

<?= $form->field($model, 'defaultStreamSort')->dropDownList($model::defaultStreamSortOptions()) ?>

<?= $form->field($model, 'defaultHideMembers')->checkbox() ?>

<?= $form->field($model, 'defaultHideActivities')->checkbox() ?>

<?= $form->field($model, 'defaultHideAbout')->checkbox() ?>

<?= $form->field($model, 'defaultHideFollowers')->checkbox() ?>

<?= $form->field($model, 'allowSpaceTopics')->checkbox(['data' => ['action-change' => 'admin.space.restrictTopicCreation']]) ?>

<?= Button::primary(Yii::t('base', 'Save'))->submit(); ?>

<?php ActiveForm::end(); ?>
