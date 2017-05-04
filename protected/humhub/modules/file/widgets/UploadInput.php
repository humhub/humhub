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
class UploadInput extends \humhub\widgets\JsWidget
{

    const DEFAULT_FORM_NAME = 'fileList';

    /**
     * javascript widget implementation.
     * 
     * @var type 
     */
    public $jsWidget = 'file.Upload';

    /**
     * @inheritdoc
     * @var type 
     */
    public $init = true;

    /**
     * Active Model can be set to attach files to this model.
     *
     * @var \yii\db\ActiveRecord
     */
    public $model;

    /**
     * Can be used to overwrite the default result input name files[] with a model
     * bound attribute formName.
     * 
     * @var string 
     */
    public $attribute;

    /**
     * Can be used to overwrite the default result input name files[] with a model
     * bound attribute formName.
     * 
     * @var string 
     */
    public $name;
    
    /**
     * Defines the input name of the submitted array field containing the result guids.
     * 
     * @var string 
     */
    public $submitName;

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
     * Used to hide/show the actual input element.
     * @var type 
     */
    public $visible = false;
    
        
    /**
     * This flag can be used in order to only allow a single guid to be submitted.
     * Note that already attached files have to be removed manually.
     * @var boolean 
     */
    public $single = false;

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        return Html::input('file', 'files[]', null, $this->getOptions());
    }

    public function getAttributes()
    {
        return [
            'multiple' => 'multiple',
            'title' => Yii::t('base', 'Upload file')
        ];
    }

    public function getData()
    {
        $formSelector = ($this->form instanceof \yii\widgets\ActiveForm) ? '#' + $this->form->getId() : $this->form;
        
        if($this->submitName) {
            $submitName = $this->submitName;
        } else {
            $submitName = ($this->model && $this->attribute) ? $this->model->formName() . '[' . $this->attribute . ']' : self::DEFAULT_FORM_NAME;
            if(!$this->single) {
                $submitName .= '[]';
            }
        }

        $result = [
            'upload-url' => $this->url,
            'upload-drop-zone' => $this->dropZone,
            'upload-progress' => $this->progress,
            'upload-preview' => $this->preview,
            'upload-form' => $formSelector,
            'upload-single' => $this->single,
            'upload-submit-name' => $submitName
        ];
        
        if ($this->model) {
            $result['upload-model'] = $this->model->className();
            $result['upload-model-id'] = $this->model->getPrimaryKey();
        }

        if ($this->max) {
            $result['max-number-of-files'] = $this->max;
            $result['max-number-of-files-message'] = Yii::t('FileModule.widgets_UploadInput', 'This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.', ['n' => $this->max]);
        }
        
        return $result;
    }
}