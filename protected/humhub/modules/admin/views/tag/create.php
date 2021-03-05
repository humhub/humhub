<?php

use humhub\modules\file\widgets\Upload;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\xcoin\models\Tag;
use kartik\widgets\Select2;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/**
 * @var Tag $model
 */

$upload = Upload::forModel($model, $model->pictureFile);
?>

<?php ModalDialog::begin(['header' => Yii::t('AdminModule.views_tag_create', 'Create Tag'), 'closable' => false]) ?>
<?php $form = ActiveForm::begin(['id' => 'tag-form']); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'name')->textInput()
                ->hint(Yii::t('AdminModule.views_tag_create', 'Please enter the tag name')) ?>
        </div>
        <div class="col-md-12">
            <?=
            $form->field($model, 'type')->widget(Select2::class, [
                'data' => Tag::getTypes(),
                'options' => ['placeholder' => '- ' . Yii::t('AdminModule.views_tag_create', 'Select tag type') . ' - '],
                'theme' => Select2::THEME_BOOTSTRAP,
                'hideSearch' => true,
                'pluginOptions' => [
                    'allowClear' => false,
                ]
            ])->hint(Yii::t('AdminModule.views_tag_create', 'Please choose the tag type')) ?>
        </div>
        <div class="col-md-12">
            <label class="control-label"><?= Yii::t('AdminModule.views_tag_create', 'Picture') ?></label><br>
            <div class="col-md-2">
                <?= $upload->button([
                    'label' => true,
                    'tooltip' => false,
                    'options' => ['accept' => 'image/*'],
                    'cssButtonClass' => 'btn-default btn-sm',
                    'dropZone' => '#tag-form',
                    'max' => 1,
                ]) ?>
            </div>
            <div class="col-md-1"></div>
            <div class="col-md-9">
                <?= $upload->preview([
                    'options' => ['style' => 'margin-top:10px'],
                    'model' => $model,
                    'showInStream' => true,
                ]) ?>
            </div>
            <br>
            <?= $upload->progress() ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?= ModalButton::submitModal(null, Yii::t('AdminModule.views_tag_create', 'Save')); ?>
    <?= ModalButton::cancel(); ?>
</div>
<?php ActiveForm::end(); ?>
<?php ModalDialog::end() ?>
