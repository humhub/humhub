<?php

use humhub\libs\Html;
use humhub\modules\admin\assets\AdminTopicAsset;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\widgets\ModalButton;
use yii\data\ActiveDataProvider;
use yii\widgets\Pjax;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var Topic $addModel
 * @var bool $suggestGlobalConversion
 */

AdminTopicAsset::register($this);

echo Html::beginTag('div', ['class' => 'panel-body']);
echo Html::tag('h4', Yii::t('AdminModule.settings', 'Topics'));
echo Html::tag('div', Yii::t('AdminModule.settings', 'Global topics can be used by all users in all Spaces. They make it easier for you to define consistent keywords throughout your entire network. If users have already created topics in Spaces, you can also convert them to global topics here.'), ['class' => 'help-block']);

Pjax::begin(['enablePushState' => false, 'id' => 'global-topics']);

$form = ActiveForm::begin(['options' => ['data-pjax' => true]]);

echo $form->field($addModel, 'name', [
    'template' => '
<div class="input-group">
{input}
<span class="input-group-btn">
    ' . Button::defaultType()->icon('add')->loader()->submit() . '
</span>
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

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover'],
    'columns' => [
        'name',
        'sort_order',
        [
            'header' => Yii::t('base', 'Actions'),
            'class' => 'yii\grid\ActionColumn',
            'options' => ['width' => '80px'],
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model) {
                    /* @var $model Topic */
                    return ModalButton::primary()->load(['edit-topic', 'id' => $model->id])->icon('edit')->xs()->loader(false);
                },
                'delete' => function ($url, $model) {
                    /* @var $model Topic */
                    return Button::danger()->icon('delete')->action('admin.topic.removeTopic', ['delete-topic', 'id' => $model->id])->confirm(
                        Yii::t('AdminModule.settings', '<strong>Confirm</strong> topic deletion'),
                        Yii::t('AdminModule.settings', 'Do you really want to delete this topic?'),
                        Yii::t('base', 'Delete')
                    )->xs()->loader(false);
                },
            ],
        ],
    ]]);

Pjax::end();

echo Html::endTag('div');
