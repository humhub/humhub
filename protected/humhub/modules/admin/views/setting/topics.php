<?php

use humhub\components\View;
use humhub\modules\topic\models\forms\TopicSettingsForm;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicTable;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/**
 * @var View $this
 * @var Topic $addModel
 * @var bool $suggestGlobalConversion
 * @var TopicSettingsForm $topicSettings
 */

ContentCard::begin([
    'title' => Yii::t('AdminModule.base', 'Topics'),
    'description' => Yii::t('AdminModule.settings', 'Global topics can be used by all users in all Spaces. They make it easier for you to define consistent keywords throughout your entire network. If users have already created topics in Spaces, you can also convert them to global topics here.'),
]);

$form = ActiveForm::begin();

echo $form->field($addModel, 'name', [
    'template' => '
<div class="input-group">
    {input}
    ' . Button::light()->icon('add')->options(['aria-label' => Yii::t('AdminModule.settings', 'Add Topic')])->loader()->submit() . '
</div>
{error}
{hint}',
    'options' => [
        'style' => 'margin-bottom: 0',
    ],
    'inputOptions' => [
        'style' => 'height:36px',
        'class' => 'form-control',
        'placeholder' => Yii::t('AdminModule.settings', 'Add Topic'),
    ],
    'errorOptions' => ['style' => ['display' => 'inline-block'], 'tag' => 'span'],
    'hintOptions' => ['style' => ['display' => 'inline-block'], 'tag' => 'span'],
]);

if ($suggestGlobalConversion) {
    echo $form->field($addModel, 'convertToGlobal')
        ->checkbox()
        ->label(Yii::t('AdminModule.settings', 'Convert to global topic'));
}

ActiveForm::end();

$form = ActiveForm::begin();
    echo $form->field($topicSettings, 'pickerVisibility')
        ->dropDownList($topicSettings->getPickerVisibilityOptions(), ['data-action-change' => 'ui.form.submit']);
ActiveForm::end();

echo TopicTable::widget();

ContentCard::end();
