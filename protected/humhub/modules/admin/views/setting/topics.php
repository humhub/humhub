<?php

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
 * @var GlobalTopicSettingForm $globalTopicSettingModel
 */

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'global-topics']); ?>
<?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
<p><?= Yii::t('AdminModule.settings', 'Add topics that you will use in your posts. Topics can be personal interests or general terms. When posting, you can select them by choosing "Topics" and it will be easier for other users to find your posts related to that topic.') ?></p>
<div class="form-group <?= $addModel->hasErrors('name') ? 'has-error' : '' ?>">
    <div class="input-group">
        <?= Html::activeTextInput($addModel, 'name', ['style' => 'height:36px', 'class' => 'form-control', 'placeholder' => Yii::t('AdminModule.settings', 'Add Topic')]) ?>
        <span class="input-group-btn">
            <?= Button::defaultType()->icon('add')->loader()->submit() ?>
        </span>
    </div>
    <?= Html::error($addModel, 'name', ['class' => 'help-block']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php $form = ActiveForm::begin([
    'id' => 'global-topics-settings-form',
    'options' => ['data-pjax' => true]
]); ?>
<?= $form->field($globalTopicSettingModel, 'restrictAdditionalTopics')
    ->checkbox() ?>
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
                    return Button::danger()->icon('delete')->action(['delete-topic', 'id' => $model->id])->confirm(
                        Yii::t('AdminModule.settings', '<strong>Confirm</strong> topic deletion'),
                        Yii::t('AdminModule.settings', 'Do you really want to delete this topic?'),
                        Yii::t('base', 'Delete')
                    )->xs()->loader(false);
                },
            ],
        ],
    ]]);


$js = <<<JS
$('#global-topics-settings-form').find('input[type="checkbox"]').on('change', function() {
    $('#global-topics-settings-form').trigger('submit');
});
JS;

$this->registerJs($js);

Pjax::end();
$this->endContent();

