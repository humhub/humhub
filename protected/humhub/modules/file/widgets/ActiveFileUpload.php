<?php

namespace humhub\modules\file\widgets;

use humhub\helpers\Html;
use humhub\modules\file\assets\ActiveFileUploadAsset;
use humhub\modules\file\models\File;
use yii\bootstrap5\InputWidget;
use yii\helpers\Url;

/**
 * Example of usage
 *
 * ```php
 * <?= $form->field($model, 'attribute')->widget(ActiveFileUpload::class. ['accept' = 'image/*', 'hideInStream' => true]) ?>
 * ```
 */
class ActiveFileUpload extends InputWidget
{
    public string $accept = '*';
    public bool $hideInStream = true;


    public function init()
    {
        parent::init();

        ActiveFileUploadAsset::register($this->view);

        $this->field->options['id'] = $this->id;
        $this->field->options['data'] = [
            'ui-widget' => 'ActiveFileUpload.Upload',
            'ui-init' => true,
        ];
        $this->field->template = '{label}{hint}{input}{error}';

        Html::addCssClass($this->field->options, ['img-uploader-field', 'well', 'p-2']);



    }

    public function run()
    {
        $uploadInput = UploadInput::widget([
            'url' => Url::to(['/file/file/upload']),
            'model' => $this->model,
            'attribute' => $this->attribute,
            'single' => true,
            'multiple' => false,
            'dropZone' => "#$this->id",
            'progress' => "#$this->id-progress",
            'preview' => "#$this->id-preview",
            'hideInStream' => $this->hideInStream,
            'options' => [
                'uploadSingle' => 1,
                'accept' => $this->accept,
            ],
        ]);

        $uploadPreview = FilePreview::widget([
            'jsWidget' => 'ActiveFileUpload.Preview',
            'id' => "$this->id-preview",
            'items' => [File::findOne(['guid' => $this->model->{$this->attribute}])],
            'options' => ['class' => 'img-uploader-preview float-start mt-3'],
            'edit' => true,
            'preventPopover' => true,
        ]);

        $uploadProgress = UploadProgress::widget(['id' => "$this->id-progress"]);

        return $this->render('activeFileUpload', [
            'uploadInput' => $uploadInput,
            'uploadPreview' => $uploadPreview,
            'uploadProgress' => $uploadProgress,
            'hiddenInput' => Html::activeHiddenInput($this->model, $this->attribute),
        ]);
    }
}
