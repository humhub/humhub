<?php

namespace humhub\modules\file\widgets;

use humhub\helpers\ArrayHelper;
use humhub\helpers\Html;
use humhub\modules\file\assets\ActiveFileUploadAsset;
use humhub\modules\file\models\File;
use yii\bootstrap5\InputWidget;
use yii\helpers\Url;

/**
 * Example of usage
 *
 * ```php
 * <?= $form->field($model->news, 'attribute')->widget(ActiveFileUpload::class, [
 *      'accept' => 'image/*',
 *      'inputOptions' => [
 *          'class' => 'class-1',
 *          //other options
 *      ],
 *      'previewOptions' => [
 *          'height' => 200,
 *          'width' => '5rem',
 *          'class' => 'class-2',
 *          //other options
 *      ],
 *      'progressOptions' => [
 *          'class' => 'class-3',
 *          //other options
 *      ],
 * ]) ?>
 * ```
 */
class ActiveFileUpload extends InputWidget
{
    public string $accept = '*';
    public bool $hideInStream = true;
    public array $inputOptions = [];
    public array $previewOptions = [];
    public array $progressOptions = [];


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

        $previewHeight = ArrayHelper::remove($this->previewOptions, 'height');
        $previewWidth = ArrayHelper::remove($this->previewOptions, 'width');

        if ($previewHeight || $previewWidth) {
            $styles = [];
            if ($previewHeight) {
                $styles['max-height'] = 'initial';
                $styles['height'] = is_numeric($previewHeight) ? "{$previewHeight}px" : $previewHeight;
            }
            if ($previewWidth) {
                $styles['max-width'] = 'initial';
                $styles['width'] = is_numeric($previewWidth) ? "{$previewWidth}px" : $previewWidth;
            }

            $styles = Html::cssStyleFromArray($styles);

            $this->view->registerCss("#$this->id .img-uploader-preview img{{$styles}}");
        }

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
            'options' => ArrayHelper::merge($this->inputOptions, [
                'uploadSingle' => 1,
                'accept' => $this->accept,
            ]),
        ]);

        $uploadPreview = FilePreview::widget([
            'jsWidget' => 'ActiveFileUpload.Preview',
            'id' => "$this->id-preview",
            'items' => [File::findOne(['guid' => $this->model->{$this->attribute}])],
            'options' => ArrayHelper::merge($this->previewOptions, ['class' => 'img-uploader-preview mt-3']),
            'edit' => true,
            'preventPopover' => true,
        ]);

        $uploadProgress = UploadProgress::widget(['id' => "$this->id-progress", 'options' => $this->progressOptions]);

        return $this->render('activeFileUpload', [
            'uploadInput' => $uploadInput,
            'uploadPreview' => $uploadPreview,
            'uploadProgress' => $uploadProgress,
            'hiddenInput' => Html::activeHiddenInput($this->model, $this->attribute),
        ]);
    }
}
