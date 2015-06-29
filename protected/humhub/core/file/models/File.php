<?php

namespace humhub\core\file\models;

use Yii;
use yii\web\UploadedFile;
use humhub\models\Setting;
use yii\helpers\Url;
use humhub\core\file\libs\ImageConverter;
use yii\base\Exception;

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
class File extends \humhub\components\ActiveRecord
{

    // Configuration
    protected $folder_uploads = "file";

    /**
     * Uploaded File or File Content
     *
     * @var UploadedFile
     */
    private $uploadedFile = null;

    /**
     * New content of the file
     *
     * @var string
     */
    public $newFileContent = null;

    /**
     * Returns all files belongs to a given HActiveRecord Object.
     * @todo Add chaching
     *
     * @param HActiveRecord $object
     * @return Array of File instances
     */
    public static function getFilesOfObject(\yii\db\ActiveRecord $object)
    {
        return self::findAll(array('object_id' => $object->getPrimaryKey(), 'object_model' => $object->className()));
    }

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

    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\UnderlyingObject::className(),
                'mustBeInstanceOf' => array(\yii\db\ActiveRecord::className()),
            ],
            [
                'class' => \humhub\components\behaviors\GUID::className(),
            ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->sanitizeFilename();

        if ($this->title == "") {
            $this->title = $this->file_name;
        }

        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        $path = $this->getPath();

        // Make really sure, that we dont delete something else :-)
        if ($this->guid != "" && $this->folder_uploads != "" && is_dir($path)) {
            $files = glob($path . DIRECTORY_SEPARATOR . "*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($path);
        }

        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        // Set new uploaded file
        if ($this->uploadedFile !== null && $this->uploadedFile instanceof UploadedFile) {
            $newFilename = $this->getPath() . DIRECTORY_SEPARATOR . $this->getFilename();

            if (is_uploaded_file($this->uploadedFile->tempName)) {
                move_uploaded_file($this->uploadedFile->tempName, $newFilename);
                @chmod($newFilename, 0744);
            }

            /**
             * For uploaded jpeg files convert them again - to handle special
             * exif attributes (e.g. orientation)
             */
            if ($this->uploadedFile->type == 'image/jpeg') {
                ImageConverter::TransformToJpeg($newFilename, $newFilename);
            }
        }

        // Set file by given contents
        if ($this->newFileContent != null) {
            $newFilename = $this->getPath() . DIRECTORY_SEPARATOR . $this->getFilename();
            file_put_contents($newFilename, $this->newFileContent);
            @chmod($newFilename, 0744);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('FileModule.models_File', 'ID'),
            'guid' => Yii::t('FileModule.models_File', 'Guid'),
            'file_name' => Yii::t('FileModule.models_File', 'File name'),
            'title' => Yii::t('FileModule.models_File', 'Title'),
            'mime_type' => Yii::t('FileModule.models_File', 'Mime Type'),
            'size' => Yii::t('FileModule.models_File', 'Size'),
            'created_at' => Yii::t('FileModule.models_File', 'Created at'),
            'created_by' => Yii::t('FileModule.models_File', 'Created By'),
            'updated_at' => Yii::t('FileModule.models_File', 'Updated at'),
            'updated_by' => Yii::t('FileModule.models_File', 'Updated by'),
        );
    }

    /**
     * Returns the Path of the File
     */
    public function getPath()
    {
        $path = Yii::getAlias('@webroot') .
                DIRECTORY_SEPARATOR . "uploads" .
                DIRECTORY_SEPARATOR . $this->folder_uploads .
                DIRECTORY_SEPARATOR . $this->guid;

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
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
     * @param string $prefix
     * @return string
     */
    public function getFilename($prefix = "")
    {
        // without prefix
        if ($prefix == "") {
            return $this->file_name;
        }

        $fileParts = pathinfo($this->file_name);

        return $fileParts['filename'] . "_" . $prefix . "." . $fileParts['extension'];
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

    public function getPreviewImageUrl($maxWidth = 1000, $maxHeight = 1000)
    {
        $prefix = 'pi_' . $maxWidth . "x" . $maxHeight;

        $originalFilename = $this->getPath() . DIRECTORY_SEPARATOR . $this->getFilename();
        $previewFilename = $this->getPath() . DIRECTORY_SEPARATOR . $this->getFilename($prefix);

        // already generated
        if (is_file($previewFilename)) {
            return $this->getUrl($prefix);
        }

        // Check file exists & has valid mime type
        if ($this->getMimeBaseType() != "image" || !is_file($originalFilename)) {
            return "";
        }

        $imageInfo = @getimagesize($originalFilename);

        // Check if we got any dimensions - invalid image
        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return "";
        }

        // Check if image type is supported
        if ($imageInfo[2] != IMAGETYPE_PNG && $imageInfo[2] != IMAGETYPE_JPEG && $imageInfo[2] != IMAGETYPE_GIF) {
            return "";
        }

        ImageConverter::Resize($originalFilename, $previewFilename, array('mode' => 'max', 'width' => $maxWidth, 'height' => $maxHeight));
        return $this->getUrl($prefix);
    }

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
        $object = $this->getUnderlyingObject();
        if ($object !== null && ($object instanceof \humhub\core\content\components\activerecords\Content || $object instanceof \humhub\core\content\components\activerecords\ContentAddon)) {
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
        $object = $this->getUnderlyingObject();
        if ($object !== null && ($object instanceof \humhub\core\content\components\activerecords\Content || $object instanceof \humhub\core\content\components\activerecords\ContentAddon)) {
            return $object->content->canWrite($userId);
        }

        // File is not bound to an object
        if ($object == null) {
            return true;
        }

        return false;
    }

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
        $this->file_name = preg_replace("/[^a-z0-9_\-s\. ]/i", "", $this->file_name);

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
        $allowedExtensions = Setting::GetText('allowedExtensions', 'file');

        if ($allowedExtensions != "") {
            $extension = $this->getExtension();
            $extension = trim(strtolower($extension));

            $allowed = array_map('trim', explode(",", Setting::GetText('allowedExtensions', 'file')));

            if (!in_array($extension, $allowed)) {
                $this->addError($attribute, Yii::t('FileModule.models_File', 'This file type is not allowed!'));
            }
        }
    }

    public function validateSize($attribute, $params)
    {
        if ($this->size > Setting::Get('maxFileSize', 'file')) {
            $this->addError($attribute, Yii::t('FileModule.models_File', 'Maximum file size ({maxFileSize}) has been exceeded!', array("{maxFileSize}" => Yii::$app->formatter->asSize(Setting::Get('maxFileSize', 'file')))));
        }
    }

    /**
     * Attaches a given list of files to an record (HActiveRecord).
     * This is used when uploading files before the record is created yet.
     *
     * @param HActiveRecord $object is a HActiveRecord
     * @param string $files is a comma seperated list of newly uploaded file guids
     */
    public static function attachPrecreated($object, $files)
    {
        if (!$object instanceof \yii\db\ActiveRecord) {
            throw new Exception("Invalid object given - require instance of HActiveRecord!");
        }

        // Attach Files
        foreach (explode(",", $files) as $fileGuid) {
            $file = self::findOne(['guid' => trim($fileGuid)]);
            if ($file != null && $file->object_model == "") {
                $file->object_model = $object->className();
                $file->object_id = $object->getPrimaryKey();
                if (!$file->save()) {
                    throw new Exception("Could not save precreated file!");
                }
            }
        }
    }

}
