<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;
use humhub\components\Widget;
use humhub\modules\file\models\File;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Base upload utility component which combines upload input/button, preview and progress components.
 *
 * An `UploadInput` serves as core upload component and is optionally linked to a progress and preview widget. An `UploadButton`
 * will wrap the input widget in a button and provides some further configurations e.g. to for styling and labels.
 *
 * If the `postState` flag is set to true (default), this component will try to fetch submitted files and auto attach them as
 * input fields to the surrounding form. This will preserve the upload state after failed form validations.
 *
 * This behavior may be deactivated or requires manual form cleanup in case the success result also contains the same upload component.
 * In such a case the `reset` flag can be activated in order to exclude all files no matter of the model or post state.
 *
 * ## Example:
 *
 * Model:
 *
 * ```
 * class MyModel extends ActiveRecord
 * {
 *      public $myFiles;
 *
 *      public function rules()
 *      {
 *          return [
 *               [['files'], 'safe']
 *          ]
 *      }
 *
 *      public function afterSave($insert, $changedAttributes)
 *      {
 *          $this->fileManager->attach($this->myFiles);
 *          parent::afterSave($insert, $changedAttributes);
 *      }
 * }
 * ```
 *
 * Controller:
 *
 * ```
 * public actionEdit($id)
 * {
 *     $model = MyModel::findOne(['id' => '$id']);
 *     if($model->load(Yii::$app->request->post()) && $model->save()) {
 *           $this->view->success();
 *           return $this->redirect(...);
 *     }
 *
 *     return $this->render(['model' => $model]);
 * }
 * ```
 *
 * View:
 *
 * ```
 * // Create initial upload component
 * $upload = Upload::forModel($model, $attribute);
 *
 *
 * // Output upload button with additional settings
 * echo $upload->button(['label' => true]);
 *
 * // Output upload progress bar widget
 * echo $upload->progress();
 *
 * // Output upload file preview widget
 * echo $upload->preview();
 * ```
 *
 * @see UploadInput
 * @see UploadButton
 * @see UploadProgress
 * @see FilePreview
 * @since 1.3.5
 */
class Upload extends Widget
{
    const DEFAULT_SUBMIT_NAME = 'fileList[]';
    const DEFAULT_UPLOAD_NAME = 'files[]';
    const DEFAULT_ATTRIBUTE_NAME = 'files';

    /**
     * @var Model the model the file array should be
     */
    public $model;

    /**
     * @var string model attribute name containing files
     */
    public $attribute;

    /**
     * @var string upload input name
     */
    public $name;

    /**
     * @var string defines the input name of file items attached to the form after the upload
     */
    public $submitName;

    /**
     * @var bool defines weather or not the component will auto attach posted file inputs to the form fetched from the
     * a post request.
     */
    public $postState = true;

    /**
     * @var int|bool max allowed file upload, if set to true a default attachment maximum will be used
     */
    public $max = true;

    /**
     * @var bool if set to true the upload component won't include any any file no matter of the model or post state
     */
    public $reset = false;

    /**
     * @var string can be set to overwrite default file upload url
     */
    public $url;

    /**
     * Static initializer for model based forms.
     *
     * @param $model
     * @param string $attribute
     * @param array $cfg
     * @return static
     * @throws \yii\base\InvalidConfigException
     */
    public static function forModel($model, $attribute = self::DEFAULT_ATTRIBUTE_NAME, $cfg = [])
    {
        return static::create(array_merge($cfg, ['model' => $model, 'attribute' => $attribute]));
    }

    /**
     * Static initializer for simple form name based uploads
     *
     * @param $submitName
     * @param string|array $uploadName
     * @param array $cfg
     * @return static
     * @throws \yii\base\InvalidConfigException
     */
    public static function withName($submitName = self::DEFAULT_SUBMIT_NAME, $uploadName = self::DEFAULT_UPLOAD_NAME, $cfg = [])
    {
        if(is_array($uploadName)) {
            $cfg = $uploadName;
            $uploadName = $submitName;
        }

        return static::create(array_merge($cfg, ['submitName' => $submitName, 'name' => $uploadName]));
    }

    /**
     * Static initializer
     *
     * @param array $cfg
     * @return static|object
     * @throws \yii\base\InvalidConfigException
     */
    public static function create($cfg = [])
    {
        $cfg['class'] = static::class;
        return Yii::createObject($cfg);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->id) {
            $this->id = $this->getId(true);
        }

        if ($this->max === true) {
            /** @var \humhub\modules\content\Module $contentModule */
            $contentModule = Yii::$app->getModule('content');
            if ($contentModule !== null) {
                $this->max = $contentModule->maxAttachedFiles;
            }
        }
    }

    /**
     * @param string
     * @return $this
     */
    public function submitName($submitName = self::DEFAULT_SUBMIT_NAME)
    {
        $this->submitName = $submitName;
        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function uploadName($uploadName = self::DEFAULT_UPLOAD_NAME)
    {
        $this->name = $uploadName;
        return $this;
    }

    /**
     * Used to define the `postState` flag which manages the input attachment of posted files.
     * If set to true (default) the upload component will try to fetch uploaded files from the request and attach input
     * fields to the surrounding form.
     *
     * @param bool $postState
     * @return $this
     */
    public function postState($postState = true)
    {
        $this->postState = $postState;
        return $this;
    }

    /**
     * If condition is true, this upload component won't include any files for the input and preview component.
     * @param bool $condition
     */
    public function reset($condition = true)
    {
        $this->reset = $condition;
    }

    /**
     * Renders a [[UploadButton]] widget for this upload.
     *
     * @param array $cfg
     * @return string
     * @throws \Exception
     * @see UploadButton
     */
    public function button($cfg = [])
    {
        $cfg = array_merge([
            'id' => $this->id,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'name' => $this->name,
            'submitName' => $this->submitName,
            'postState' => $this->postState && !$this->reset,
            'url' => $this->url
        ], $cfg);

        return UploadButton::widget($cfg);
    }

    /**
     * Renders a [[UploadInput]] widgets for this upload.
     *
     * The input widget doesn't have to be rendered manually if the result of `button()` was already included.
     *
     * @param array $cfg
     * @return string
     * @throws \Exception
     * @see UploadInput
     */
    public function input($cfg = [])
    {
        $cfg = array_merge([
            'id' => $this->id,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'name' => $this->name,
            'submitName' => $this->submitName,
            'postState' => $this->postState && !$this->reset
        ], $cfg);

        return UploadInput::widget($cfg);
    }

    /**
     * Renders a progress bar component for this upload.
     *
     * The resulting progress bar widget will have an id of the following form:
     *
     * `<upload_id>_progress`
     *
     * @param array $cfg
     * @return string
     * @throws \Exception
     * @see UploadProgress
     */
    public function progress($cfg = [])
    {
        $options = (isset($cfg['options'])) ? $cfg['options'] : [];
        $options['id'] = $this->id.'_progress';
        $cfg['options'] = $options;

        return UploadProgress::widget($cfg);
    }

    /**
     * Renders a preview component for this upload.
     *
     * The resulting preview widget will have an id of the following form:
     *
     * `<upload_id>_preview`
     *
     * @param array $cfg
     * @return string
     * @throws \Exception
     * @see FilePreview
     */
    public function preview($cfg = [])
    {
        $options = (isset($cfg['options'])) ? $cfg['options'] : [];
        $options['id'] = $this->id.'_preview';
        $cfg['options'] = $options;

        $cfg = array_merge([
            'items' => $this->getPreviewFiles(isset($cfg['showInStream']) ? $cfg['showInStream'] : null),
            'model' => $this->model,
            'attribute' => $this->attribute,
            'edit' => true
        ], $cfg);

        return FilePreview::widget($cfg);
    }

    /**
     * Assembles files for the preview widget.
     *
     * @param null $showInStream
     * @return array
     */
    protected function getPreviewFiles($showInStream = null)
    {
        if($this->reset) {
            return [];
        }


        $resultMap = [];

        // Try fetching submitted files for this upload component if postState flag is active
        if($this->postState) {
            $resultMap = [];
            $postFiles = UploadInput::getSubmittedFiles($this->model, $this->attribute, $this->submitName);
            foreach ($postFiles as $postFile) {
                $resultMap[$postFile->guid] = $postFile;
            }
        }

        // Add already attached files
        $modelFiles = $this->getModelFiles($showInStream);
        if(!empty($modelFiles)) {
            foreach ($modelFiles as $attachedFile) {
                $resultMap[$attachedFile->guid] = $attachedFile;
            }
        }

        return array_values($resultMap);
    }

    private function getModelFiles($showInStream = null)
    {
        if($this->model instanceof ActiveRecord) {
            return($showInStream === null)
                ? $this->model->fileManager->findAll()
                : $this->model->fileManager->findStreamFiles($showInStream);
        }

        $result = [];
        if($this->model && $this->attribute) {
            $files = Html::getAttributeValue($this->model, $this->attribute);
            if(is_array($files)) {
                foreach ($files as $file) {
                    $file = is_string($file) ? File::findOne(['guid' => $file]) : $file;
                    if($file) {
                        $result[] = $file;
                    }
                }
            }
        }

        return $result;
    }
}
