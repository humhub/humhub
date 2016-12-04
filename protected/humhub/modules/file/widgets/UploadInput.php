<?php

namespace humhub\modules\file\widgets;

use Yii;
use yii\helpers\Html;

/**
 * The file input will upload files either to the given $url or to the default
 * file upload route '/file/file/upload'. 
 * 
 * The returned guids will be attached to an array input field with a default form name 'guids[]'.
 * The default form name can either be overwritten by providing a $model and $attribute or by setting the
 * $name field.
 * 
 * The resulted guids will either be appended to the surrounding form if no $form selector is given.
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadInput extends \yii\base\Widget
{

    const DEFAULT_FORM_NAME = 'guids[]';

    /**
     * @var String unique id of this uploader
     */
    public $id;

    /**
     * javascript widget implementation.
     * 
     * @var type 
     */
    public $jsWidget = 'file.Upload';

    /**
     * Active Model can be set to attach files to this model.
     *
     * @var \yii\db\ActiveRecord
     */
    public $model;

    /**
     * Can be used to overwrite the default result input name guids[] with a model
     * bound attribute formName.
     * 
     * @var string 
     */
    public $attribute;

    /**
     * Can be used to overwrite the default result input name guids[] with a model
     * bound attribute formName.
     * 
     * @var string 
     */
    public $name;

    /**
     * Can be set if the upload button is not contained in the form itself.
     * 
     * @var type 
     */
    public $form;

    /**
     * Can be set to overwrite the default file upload route.
     * 
     * @var type 
     */
    public $url;

    /**
     * Maximum amount of allowed uploads.
     * @var type 
     */
    public $max;

    /**
     * Selector of dropzone node.
     * @var type 
     */
    public $dropZone;

    /**
     * File preview js widget selector.
     * 
     * @var type 
     */
    public $preview;

    /**
     * Upload progress js widget selector.
     * @var type 
     */
    public $progress;

    /**
     * Additional input field attributes.
     * @var type 
     */
    public $options = [];

    /**
     * Used to hide/show the actual input element.
     * @var type 
     */
    public $hidden = true;

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        return Html::input('file', 'files[]', null, $this->getOptions());
    }

    public function getOptions()
    {
        $formSelector = ($this->form instanceof \yii\widgets\ActiveForm) ? '#' + $this->form->getId() : $this->form;
        $resultFieldName = ($this->model && $this->attribute) ? $this->model->formName() + '[' + $this->attribute + '][]' : self::DEFAULT_FORM_NAME;
        $style = ($this->hidden) ? 'display:none;' : '';

        $defaultOptions = [
            'id' => $this->id,
            'multiple' => 'multiple',
            'style' => $style,
            'data' => [
                'ui-widget' => $this->jsWidget,
                'ui-init' => '',
                'upload-url' => $this->url,
                'upload-drop-zone' => $this->dropZone,
                'upload-progress' => $this->progress,
                'upload-preview' => $this->preview,
                'upload-form' => $formSelector,
                'result-field-name' => $resultFieldName
            ]
        ];

        if ($this->model) {
            $defaultOptions['data']['upload-model'] = $this->model->className();
            $defaultOptions['data']['upload-model-id'] = $this->model->getPrimaryKey();
        }
        
        if($this->max) {
            $defaultOptions['data']['max-number-of-files'] = $this->max;
            $defaultOptions['data']['max-number-of-files-message'] =  Yii::t('FileModule.widgets_UploadInput', 'This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.', ['n' => $this->max]);
        
        }

        return \yii\helpers\ArrayHelper::merge($defaultOptions, $this->options);
    }

}

?>