<?php

use humhub\modules\admin\assets\AdminTopicAsset;
use humhub\modules\admin\models\forms\GlobalTopicSettingForm;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\widgets\ModalButton;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var Topic $addModel
 * @var bool $suggestGlobalConversion
 */

AdminTopicAsset::register($this);

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'global-topics']); ?>
<?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
<p><?= Yii::t('AdminModule.settings', 'Add topics that you will use in your posts. Topics can be personal interests or general terms. When posting, you can select them by choosing "Topics" and it will be easier for other users to find your posts related to that topic.') ?></p>

<?= $form->field($addModel, 'name', [
    'template' => '
<div class="input-group">
{input}
<span class="input-group-btn">
    ' . Button::defaultType()->icon('add')->loader()->submit() . '
</span>
</div>
{error}
{hint}',
    'inputOptions' => [
        'style' => 'height:36px',
        'class' => 'form-control',
        'placeholder' => Yii::t('AdminModule.settings', 'Add Topic'),
    ],
    'errorOptions' => ['style' => ['display' => 'inline-block'], 'tag' => 'span'],
    'hintOptions' => ['style' => ['display' => 'inline-block'], 'tag' => 'span'],
])->hint($suggestGlobalConversion
    ? Button::info()->setText(Yii::t('AdminModule.settings', 'Convert \'{topicName}\' into global', ['topicName' => $addModel->name]))
        ->action('admin.topic.convertTopic')
        ->xs()->loader(false)
        ->confirm(
            Yii::t('AdminModule.settings', '<strong>Confirm</strong> topic conversion'),
            Yii::t('AdminModule.settings', 'Do you really want to convert \'{topicName}\' into global?', ['topicName' => $addModel->name]),
            Yii::t('base', 'Convert')
        )
    : '') ?>
<?= Html::hiddenInput('convert-to-global', 0) ?>
<?php ActiveForm::end(); ?>

<?= GridView::widget([
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
$this->endContent();

