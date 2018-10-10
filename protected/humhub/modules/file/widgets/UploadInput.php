<?php

namespace humhub\modules\file\widgets;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use humhub\modules\file\models\File;
use humhub\widgets\JsWidget;

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
class UploadInput extends JsWidget
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
     * Name of the upload input field default files[].
     *
     * @var string 
     */
    public $name = 'files[]';

    /**
     * Will automatically add posted files with the given $submitName to the form as hidden input.
     *
     * This sometimes is required for forms with backend validation. Note in case of a successful submission the files
     * will also be added if the form is included visible in the resulting view.
     *
     * @var bool
     * @since 1.3.5
     */
    public $postState = false;
    
    /**
     * Defines the input name of attached file list items.
     *
     * If no model is given the default name is `fileList[]`
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
     * Total number of maximum amount of allowed file uploads.
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
     * @var boolean defines if uploaded files should set the show_in_stream flag, this has only effect if the underlying action does support the showInStream request parameter
     */
    public $hideInStream = false;
    
        
    /**
     * This flag can be used in order to only allow a single guid to be submitted.
     * Note that already attached files have to be removed manually.
     * @var boolean 
     */
    public $single = false;

    /**
     * Sets the multiple flag of the file input
     * @var bool
     */
    public $multiple = true;

    /**
     * @var bool defines if the file should be attached to the given §model right after upload, note this only works for
     * already existing models
     */
    public $attach = true;

    public function init()
    {
        parent::init();

        if(!$this->submitName) {
            $this->submitName = ($this->model && $this->attribute) ? $this->model->formName() . '[' . $this->attribute . ']' : self::DEFAULT_FORM_NAME;
            if(!$this->single) {
                $this->submitName .= '[]';
            }
        }
    }

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        $result = Html::input('file', $this->name, null, $this->getOptions());

        if($this->postState) {
            foreach (static::getSubmittedFiles($this->model, $this->attribute, $this->submitName) as $file) {
                $result .= Html::hiddenInput($this->submitName, $file->guid);
            }
        }

        return $result;
    }

    /**
     * Returns submitted files for the given settings, either bei fetching them directly from the
     * request in case of form-name based uploads or from the model in case of model based uploads.
     *
     * @param $model Model
     * @param $attribute string
     * @param $submitName string
     * @return array
     * @since 1.3.5
     */
    public static function getSubmittedFiles($model, $attribute, $submitName)
    {
        $files = [];
        if($model && $attribute) {
            $files = Html::getAttributeValue($model, $attribute);
        } else if($submitName) {
            $postSubmit = $submitName;

            if(static::endsWith('[]', $postSubmit)) {
                $postSubmit = substr($postSubmit, 0, -2);
            }

            $files = Yii::$app->request->post($postSubmit);

            if(!$files) {
                return [];
            }

            if(!is_array($files)) {
                $files = [$files];
            }
        }

        $result = [];
        foreach ($files as $file) {
            $result[] = is_string($file) ? File::findOne(['guid' => $file]) : $file;
        }

        return $result;
    }

    private static function endsWith($needle, $haystack)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    public function getAttributes()
    {
        return [
            'multiple' => ($this->multiple) ? 'multiple' : null,
            'title' => Yii::t('base', 'Upload file')
        ];
    }

    public function getData()
    {
        $formSelector = ($this->form instanceof \yii\widgets\ActiveForm) ? '#' + $this->form->getId() : $this->form;

        $result = [
            'upload-url' => $this->url,
            'upload-drop-zone' => $this->dropZone,
            'upload-progress' => $this->progress,
            'upload-preview' => $this->preview,
            'upload-form' => $formSelector,
            'upload-single' => $this->single,
            'upload-submit-name' => $this->submitName,
            'upload-hide-in-stream' => $this->hideInStream ? '1' : null
        ];

        if($this->hideInStream) {
            $result['upload-hide-in-stream'] = '1';
        }
        
        if ($this->model instanceof \yii\db\ActiveRecord && $this->attach) {
            $result['upload-model'] = $this->model->className();
            $result['upload-model-id'] = $this->model->getPrimaryKey();
        }

        $result['php-max-file-uploads'] = ini_get('max_file_uploads');
        $result['php-max-file-uploads-message'] = Yii::t('FileModule.widgets_UploadInput', 'Sorry, you can only upload up to {n,plural,=1{# file} other{# files}} at once.', ['n' => $result['php-max-file-uploads']]);

        if ($this->max) {
            $result['max-number-of-files'] = $this->max;
            $result['max-number-of-files-message'] = Yii::t('FileModule.widgets_UploadInput', 'This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.', ['n' => $this->max]);
        }
        
        return $result;
    }
}
