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
 * Following properties are optional and for module depended use:
 * - title
 * 
 * @since 0.5
 */
class File extends FileCompat
{

    /**
     * @var \humhub\modules\file\components\StorageManagerInterface the storage manager
     */
    private $_store = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mime_type'], 'string', 'max' => 150],
            [['mime_type'], 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9\.ä\/\-\+]/', 'message' => Yii::t('FileModule.base', 'Invalid Mime-Type')],
            [['file_name', 'title'], 'string', 'max' => 255],
            [['size'], 'integer'],
        ];
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
    public function beforeDelete()
    {
        $this->store->delete();
        return parent::beforeDelete();
    }

    /**
     * Returns the url to this file
     * 
     * Available params (see also: DownloadAction)
     * - variant: the requested file variant 
     * - download: force download option (default: false)
     * 
     * @param array $params the params
     * @param boolean $absolute
     * @return string the url to the file download
     */
    public function getUrl($params = [], $absolute = true)
    {
        // Handle old 'suffix' attribute for HumHub prior 1.1 versions
        if (is_string($params)) {
            $suffix = $params;
            $params = [];
            if ($suffix != '') {
                $params['variant'] = $suffix;
            }
        }

        $params['guid'] = $this->guid;
        array_unshift($params, '/file/file/download');
        return Url::to($params, $absolute);
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
     * If the file is not an instance of ContentActiveRecord or ContentAddonActiveRecord
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
            $this->_store = Yii::createObject(Yii::$app->getModule('file')->storageManagerClass);
            $this->_store->setFile($this);
        }

        return $this->_store;
    }
    
    /**
     * Returns all attached Files of the given $record.
     * 
     * @param \yii\db\ActiveRecord $record
     * @return File[]
     */
    public static function findByRecord(\yii\db\ActiveRecord $record)
    {
        return self::findAll(['object_model' => $record->className(), 'object_id' => $record->getPrimaryKey()]);
    }

}
