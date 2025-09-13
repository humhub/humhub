<?php

namespace humhub\modules\file\widgets;

use humhub\helpers\Html;
use humhub\modules\file\assets\ImgUploadFieldAsset;
use humhub\modules\file\models\File;
use yii\bootstrap5\InputWidget;
use yii\helpers\Url;

/**
 * Example of usage
 *
 * ```php
 * <?= $form->field($model, 'attribute')->widget(ImageUpload::class. ['hideInStream' => true]) ?>
 * ```
 */
class ImageUpload extends InputWidget
{
    public bool $hideInStream = true;


    public function init()
    {
        parent::init();

        ImgUploadFieldAsset::register($this->view);

        $this->field->options['id'] = $this->id;
        $this->field->options['data'] = [
            'ui-widget' => 'imgUploadField.ImgUpload',
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
                'accept' => 'image/*'
            ],
        ]);

        $uploadPreview = FilePreview::widget([
            'jsWidget' => 'imgUploadField.Preview',
            'id' => "$this->id-preview",
            'items' => [File::findOne(['guid' => $this->model->{$this->attribute}])],
            'options' => ['class' => 'img-uploader-preview float-start mt-3'],
            'edit' => true,
            'preventPopover' => true,
        ]);

        $uploadProgress = UploadProgress::widget(['id' => "$this->id-progress"]);

        return $this->render('imageUpload', [
            'uploadInput' => $uploadInput,
            'uploadPreview' => $uploadPreview,
            'uploadProgress' => $uploadProgress,
            'hiddenInput' => Html::activeHiddenInput($this->model, $this->attribute),
        ]);
    }
}
