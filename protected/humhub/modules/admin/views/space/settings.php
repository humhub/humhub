<?php

use humhub\libs\Html;
use humhub\modules\admin\assets\AdminSpaceAsset;
use humhub\modules\admin\models\forms\SpaceSettingsForm;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

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
    ]
]);

echo Html::tag('h4', Yii::t('AdminModule.space', 'Space Settings'));
echo Html::tag(
    'p',
    Yii::t('AdminModule.space', 'Here you can define your default settings for new spaces. These settings can be overwritten for each individual space.'),
    ['class' => 'help-block']
);

$form = ActiveForm::begin(['id' => 'space-settings-form', 'acknowledge' => true]);
echo SpacePickerField::widget([
    'form' => $form,
    'model' => $model,
    'attribute' => 'defaultSpaceGuid',
    'selection' => $model->defaultSpaces
]);
echo $form->field($model, 'defaultVisibility')->dropDownList($visibilityOptions);
echo $form->field($model, 'defaultJoinPolicy')->dropDownList($joinPolicyOptions, ['disabled' => $model->defaultVisibility == 0]);
echo $form->field($model, 'defaultContentVisibility')->dropDownList($contentVisibilityOptions, ['disabled' => $model->defaultVisibility == 0]);
echo $form->field($model, 'defaultIndexRoute')->dropDownList($indexModuleSelection);
echo $form->field($model, 'defaultIndexGuestRoute')->dropDownList($indexModuleSelection);
echo $form->field($model, 'defaultStreamSort')->dropDownList($model::defaultStreamSortOptions());
echo $form->field($model, 'defaultHideMembers')->checkbox();
echo $form->field($model, 'defaultHideActivities')->checkbox();
echo $form->field($model, 'defaultHideAbout')->checkbox();
echo $form->field($model, 'defaultHideFollowers')->checkbox();
echo $form->field($model, 'allowSpaceTopics')->checkbox(['data' => ['action-change' => 'admin.space.restrictTopicCreation']]);
echo Button::primary(Yii::t('base', 'Save'))->submit();
ActiveForm::end();
