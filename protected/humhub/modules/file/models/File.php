<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\Url;
use humhub\modules\file\libs\ImageConverter;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 * @property integer $id
 * @property string $guid
 * @property string $file_name
 * @property string $title
 * @property string $mime_type
 * @property string $size
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.file.models
 * @since 0.5
 */
class File extends FileCompat
{

    /**
     * @var UploadedFile the uploaded file
     */
    private $uploadedFile = null;

    /**
     * @var string file content 
     */
    public $newFileContent = null;

    /**
     * @var \humhub\modules\file\components\StorageManagerInterface the storage manager
     */
    private $_store = null;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array(['created_by', 'updated_by', 'size'], 'integer'),
            array(['guid'], 'string', 'max' => 45),
            array(['mime_type'], 'string', 'max' => 150),
            array('filename', 'validateExtension'),
            array('filename', 'validateSize'),
            array('mime_type', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9\.ä\/\-]/', 'message' => Yii::t('FileModule.models_File', 'Invalid Mime-Type')),
            array(['file_name', 'title'], 'string', 'max' => 255),
            array(['created_at', 'updated_at'], 'safe'),
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => array(\humhub\components\ActiveRecord::className()),
            ],
            [
                'class' => \humhub\components\behaviors\GUID::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->sanitizeFilename();

        if ($this->title == "") {
            $this->title = $this->file_name;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->store->delete();
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Set file(content) if provided
        if ($this->uploadedFile !== null && $this->uploadedFile instanceof UploadedFile) {
            $this->store->set($this->uploadedFile);
        } elseif ($this->newFileContent != null) {
            $this->store->setContent($this->newFileContent);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns the Url of the File
     *
     * @param string $suffix
     * @param boolean $absolute
     * @return string
     */
    public function getUrl($suffix = "", $absolute = true)
    {
        $params = array();
        $params['guid'] = $this->guid;
        if ($suffix) {
            $params['suffix'] = $suffix;
        }

        array_unshift($params, '/file/file/download');

        return Url::to($params, $absolute);
    }

    /**
     * Returns the filename
     *
     * @param string $suffix
     * @return string
     */
    public function getFilename($suffix = "")
    {
        // without prefix
        if ($suffix == "") {
            return $this->file_name;
        }

        $fileParts = pathinfo($this->file_name);

        return $fileParts['filename'] . "_" . $suffix . "." . $fileParts['extension'];
    }

    public function getMimeBaseType()
    {
        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $baseType;
        }

        return "";
    }

    public function getMimeSubType()
    {
        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $subType;
        }

        return "";
    }

    /**
     * Returns the extension of the uploaded file
     * 
     * @return string the extension
     */
    public function getExtension()
    {
        $fileParts = pathinfo($this->file_name);
        if (isset($fileParts['extension'])) {
            return $fileParts['extension'];
        }
        return '';
    }

    /**
     * Checks if given file can read.
     *
     * If the file is not an instance of HActiveRecordContent or HActiveRecordContentAddon
     * the file is readable for all.
     */
    public function canRead($userId = "")
    {
        $object = $this->getPolymorphicRelation();
        if ($object !== null && ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord)) {
            return $object->content->canRead($userId);
        }

        return true;
    }

    /**
     * Checks if given file can deleted.
     *
     * If the file is not an instance of HActiveRecordContent or HActiveRecordContentAddon
     * the file is readable for all unless there is method canWrite or canDelete implemented.
     */
    public function canDelete($userId = "")
    {
        $object = $this->getPolymorphicRelation();
        if ($object != null) {
            if ($object instanceof ContentAddonActiveRecord) {
                return $object->canWrite($userId);
            } else if ($object instanceof ContentActiveRecord) {
                return $object->content->canWrite($userId);
            }
        }

        if ($object !== null && ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord)) {
            return $object->content->canWrite($userId);
        }

        // File is not bound to an object
        if ($object == null) {
            return true;
        }

        return false;
    }

    /**
     * Sets uploaded file to this file model
     * 
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile)
    {
        $this->file_name = $uploadedFile->name;
        $this->mime_type = $uploadedFile->type;
        $this->size = $uploadedFile->size;
        $this->uploadedFile = $uploadedFile;
    }

    public function sanitizeFilename()
    {
        $this->file_name = trim($this->file_name);

        // Ensure max length
        $pathInfo = pathinfo($this->file_name);
        if (strlen($pathInfo['filename']) > 60) {
            $pathInfo['filename'] = substr($pathInfo['filename'], 0, 60);
        }

        $this->file_name = $pathInfo['filename'];

        if ($this->file_name == "") {
            $this->file_name = "Unnamed";
        }

        if (isset($pathInfo['extension']))
            $this->file_name .= "." . trim($pathInfo['extension']);
    }

    public function validateExtension($attribute, $params)
    {
        $allowedExtensions = Yii::$app->getModule('file')->settings->get('allowedExtensions');

        if ($allowedExtensions != "") {
            $extension = $this->getExtension();
            $extension = trim(strtolower($extension));

            $allowed = array_map('trim', explode(",", Yii::$app->getModule('file')->settings->get('allowedExtensions')));

            if (!in_array($extension, $allowed)) {
                $this->addError($attribute, Yii::t('FileModule.models_File', 'This file type is not allowed!'));
            }
        }
    }

    public function validateSize($attribute, $params)
    {
        if ($this->size > Yii::$app->getModule('file')->settings->get('maxFileSize')) {
            $this->addError($attribute, Yii::t('FileModule.models_File', 'Maximum file size ({maxFileSize}) has been exceeded!', array("{maxFileSize}" => Yii::$app->formatter->asSize(Yii::$app->getModule('file')->settings->get('maxFileSize')))));
        }
        // check if the file can be processed with php image manipulation tools in case it is an image
        if (isset($this->uploadedFile) && in_array($this->uploadedFile->type, [image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG)]) && !ImageConverter::allocateMemory($this->uploadedFile->tempName, true)) {
            $this->addError($attribute, Yii::t('FileModule.models_File', 'Image dimensions are too big to be processed with current server memory limit!'));
        }
    }

    public function getInfoArray()
    {
        $info = [];

        $info['error'] = false;
        $info['guid'] = $this->guid;
        $info['name'] = $this->file_name;
        $info['title'] = $this->title;
        $info['size'] = $this->size;
        $info['mimeIcon'] = \humhub\libs\MimeHelper::getMimeIconClassByExtension($this->getExtension());
        $info['mimeBaseType'] = $this->getMimeBaseType();
        $info['mimeSubType'] = $this->getMimeSubType();
        $info['url'] = $this->getUrl("", false);
        $info['thumbnailUrl'] = $this->getPreviewImageUrl(200, 200);

        return $info;
    }

    /**
     * Checks if this file record is assigned and used by record
     * 
     * @return boolean is whether in use or not
     */
    public function isAssigned()
    {
        return ($this->object_model != "");
    }

    /**
     * Returns the StorageManager
     * 
     * @return \humhub\modules\file\components\StorageManager
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->_store = new \humhub\modules\file\components\StorageManager();
            $this->_store->setFile($this);
        }

        return $this->_store;
    }

}
