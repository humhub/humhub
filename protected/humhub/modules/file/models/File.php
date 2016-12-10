<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\base\Exception;

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
     * @param \yii\db\ActiveRecord $object
     * @return array of File instances
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
            array(['size'], 'integer'),
            array(['guid'], 'string', 'max' => 45),
            array(['mime_type'], 'string', 'max' => 150),
            array('filename', 'validateExtension'),
            array('filename', 'validateSize'),
            array('mime_type', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9\.ä\/\-\+]/', 'message' => Yii::t('FileModule.models_File', 'Invalid Mime-Type')),
            array(['file_name', 'title'], 'string', 'max' => 255),
        );
    }

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
            $newFilename = $this->getStoredFilePath();
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
            $newFilename = $this->getStoredFilePath();
            file_put_contents($newFilename, $this->newFileContent);
            @chmod($newFilename, 0744);
        }

        return parent::afterSave($insert, $changedAttributes);
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

        return $fileParts['filename'] . '_' . $suffix . (!empty($fileParts['extension']) ? '.' . $fileParts['extension'] : '');
    }

    /**
     * Returns the file and path to the stored file
     */
    public function getStoredFilePath($suffix = '')
    {
        /*
        // Fallback for older versions
        $oldFile = $this->getPath() . DIRECTORY_SEPARATOR . $this->getFilename($suffix);
        if (file_exists($oldFile)) {
            return $oldFile;
        }
        *
        */

        $suffix = preg_replace("/[^a-z0-9_]/i", "", $suffix);

        $file = ($suffix == '') ? 'file' : $suffix;
        return $this->getPath() . DIRECTORY_SEPARATOR . $file;
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
        if($this->isMimeType('image/svg+xml')) {
            return $this->getUrl();
        }
        
        $suffix = 'pi_' . $maxWidth . "x" . $maxHeight;

        $originalFilename = $this->getStoredFilePath();
        $previewFilename = $this->getStoredFilePath($suffix);

        // already generated
        if (is_file($previewFilename)) {
            return $this->getUrl($suffix);
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
        return $this->getUrl($suffix);
    }
    
    public function isMimeType($mime)
    {
        return $this->mime_type === $mime;
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
        if($object != null) {
            if ($object instanceof ContentAddonActiveRecord) {
                return $object->canWrite($userId);
            }  else if ($object instanceof ContentActiveRecord) {
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
        if(isset($this->uploadedFile) && in_array($this->uploadedFile->type, [image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG)]) && !ImageConverter::allocateMemory($this->uploadedFile->tempName, true)) {
            $this->addError($attribute, Yii::t('FileModule.models_File', 'Image dimensions are too big to be processed with current server memory limit!'));
        }
    }

    /**
     * Attaches a given list of files to an record (HActiveRecord).
     * This is used when uploading files before the record is created yet.
     *
     * @param \yii\db\ActiveRecord $object is a HActiveRecord
     * @param string $files is a comma seperated list of newly uploaded file guids
     */
    public static function attachPrecreated($object, $files)
    {
        if (!$object instanceof \yii\db\ActiveRecord) {
            throw new Exception('Invalid object given - require instance of \yii\db\ActiveRecord!');
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

}
