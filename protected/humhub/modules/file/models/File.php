<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\UploadedFile;

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
 * @property integer $content_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $show_in_stream
 * @property string $hash_sha1
 *
 * @property \humhub\modules\user\models\User $createdBy
 * @property \humhub\modules\file\components\StorageManager $store
 * @property FileHistory[] $historyFiles
 *
 * @mixin PolymorphicRelation
 * @mixin GUID
 *
 * Following properties are optional and for module depended use:
 * - title
 *
 * @since 0.5
 */
class File extends FileCompat
{
    /**
     * @event Event that is triggered after a new file content has been stored.
     */
    const EVENT_AFTER_NEW_STORED_FILE = 'afterNewStoredFile';

    /**
     * @var int $old_updated_by
     */
    public $old_updated_by;

    /**
     * @var string $old_updated_at
     */
    public $old_updated_at;

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
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [ActiveRecord::class],
            ],
            [
                'class' => GUID::class,
            ],
        ];
    }

    /**
     * Gets query for [[FileHistory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHistoryFiles()
    {
        return $this->hasMany(FileHistory::class, ['file_id' => 'id'])->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->old_updated_by = $this->getOldAttribute('updated_by');
        $this->old_updated_at = $this->getOldAttribute('updated_at');

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->store->delete();
        FileHistory::deleteAll(['file_id' => $this->id]);

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
        $params['hash_sha1'] = $this->getHash(8);
        array_unshift($params, '/file/file/download');
        return Url::to($params, $absolute);
    }

    /**
     * Get hash
     *
     * @param int Return number of first chars of the file hash, 0 - unlimit
     * @return string
     */
    public function getHash($length = 0)
    {
        if (empty($this->hash_sha1) && $this->store->has()) {
            $this->updateAttributes(['hash_sha1' => sha1_file($this->store->get())]);
        }

        return $length ? substr($this->hash_sha1, 0, $length) : $this->hash_sha1;
    }

    /**
     * Checks if given file can read.
     *
     * If the file is not an instance of HActiveRecordContent or HActiveRecordContentAddon
     * the file is readable for all.
     * @param string|User $userId
     * @return bool
     */
    public function canRead($userId = "")
    {
        $object = $this->getPolymorphicRelation();
        if ($object !== null && ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord)) {
            return $object->content->canView($userId);
        }

        return true;
    }

    /**
     * Checks if given file can deleted.
     *
     * If the file is not an instance of ContentActiveRecord or ContentAddonActiveRecord
     * the file is readable for all unless there is method canEdit or canDelete implemented.
     */
    public function canDelete($userId = null)
    {
        $object = $this->getPolymorphicRelation();

        // File is not bound to an object
        if ($object === null) {
            return true;
        }

        if ($object instanceof ContentAddonActiveRecord) {
            /** @var ContentAddonActiveRecord $object */
            return $object->canEdit($userId) || $object->content->canEdit($userId);
        } elseif ($object instanceof ContentActiveRecord) {
            /** @var ContentActiveRecord $object */
            return $object->content->canEdit($userId);
        } elseif ($object instanceof ActiveRecord && method_exists($object, 'canEdit')) {
            /** @var ActiveRecord $object */
            return $object->canEdit($userId);
        }

        return false;
    }

    /**
     * Checks if this file record is already attached to record.
     *
     * @return boolean is whether in use or not
     */
    public function isAssigned()
    {
        return ($this->object_model != "");
    }

    /**
     * Checks if this file is attached to the given record
     * @param ActiveRecord $record
     * @return bool
     */
    public function isAssignedTo(ActiveRecord $record)
    {
        return $this->object_model === get_class($record) && $this->object_id == $record->getPrimaryKey();
    }

    /**
     * Returns the StorageManager
     *
     * @return \humhub\modules\file\components\StorageManagerInterface
     * @throws \yii\base\InvalidConfigException
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

    /**
     * Get File History by ID
     *
     * @param int $fileHistoryId
     * @return FileHistory|null
     */
    public function getFileHistoryById($fileHistoryId): ?FileHistory
    {
        if (empty($fileHistoryId) || $this->isNewRecord) {
            return null;
        }

        return FileHistory::findOne(['id' => $fileHistoryId, 'file_id' => $this->id]);
    }

    /**
     * Sets a new file content based on an UploadedFile, new File or a file path.
     *
     * @param UploadedFile|File|string $file File object or path
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     * @since 1.10
     */
    public function setStoredFile($file, $skipHistoryEntry = false)
    {
        $this->beforeNewStoredFile($skipHistoryEntry);

        if ($file instanceof UploadedFile) {
            $this->store->set($file);
        } elseif ($file instanceof File) {
            if ($file->isAssigned()) {
                throw new InvalidArgumentException('Already assigned File records cannot stored as another File record.');
            }
            $this->store->setByPath($file->store->get());
            $file->delete();
        } elseif (is_string($file) && is_file($file)) {
            $this->store->setByPath($file);
        }

        $this->afterNewStoredFile();
    }


    /**
     * Sets a new file content by a given string.
     *
     * @param string $content
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     * @since 1.10
     */
    public function setStoredFileContent($content, $skipHistoryEntry = false)
    {
        $this->beforeNewStoredFile($skipHistoryEntry);
        $this->store->setContent($content);
        $this->afterNewStoredFile();
    }

    /**
     * Steps that must be executed before a new file content is set.
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     */
    private function beforeNewStoredFile(bool $skipHistoryEntry)
    {
        if ($this->isNewRecord) {
            throw new Exception('File Record must be saved before setting a new file content.');
        }

        if ($this->store->has() && FileHistory::isEnabled($this) && !$skipHistoryEntry) {
            FileHistory::createEntryForFile($this);
        }

        $this->store->delete(null, [FileHistory::VARIANT_PREFIX . '*']);
    }

    /**
     * Steps that must be performed after a new file content has been set.
     */
    private function afterNewStoredFile()
    {
        if ($this->store->has()) {
            // Make sure to update updated_by & updated_at and avoid save()
            $this->beforeSave(false);

            $this->updateAttributes([
                'hash_sha1' => sha1_file($this->store->get()),
                'size' => filesize($this->store->get()),
                'updated_by' => $this->updated_by,
                'updated_at' => $this->updated_at,
            ]);
            $this->trigger(self::EVENT_AFTER_NEW_STORED_FILE);
        }
    }
}
