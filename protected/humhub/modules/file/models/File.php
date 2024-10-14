<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\interfaces\ViewableInterface;
use humhub\libs\StdClass;
use humhub\libs\UUIDValidator;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\file\components\StorageManager;
use humhub\modules\file\components\StorageManagerInterface;
use humhub\modules\file\libs\Metadata;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * The following are the available columns in table 'file':
 *
 * @property int $id
 * @property string $guid
 * @property int $state
 * @property int|null $category Note, categories are still experimental. Expect changes in v1.16 (ToDo)
 * @property string $file_name
 * @property string $title
 * @property string $mime_type
 * @property string $size
 * @property-read Metadata $metadata since 1.15. Note, $metadata is still experimental. Expect changes in v1.16 (ToDo).
 *      This property is read-only in the sense that no new instance be assigned to the model.
 *      Edit data always by working on the object itself.
 *      You best retrieve is using `static::getMetadata()`.
 *      E.g, to set a value you could do:
 * ```
 *      // setting a single value
 *      $model->getMetadata()->property1 = "some value";
 *      // or
 *      $model->getMetadata()['property2'] = "some other value";
 *
 *      // setting multiple values
 *      $metadata = $model->getMetadata();
 *      $metadata->property1 = "some value";
 *      $metadata['property2'] = "some other value";
 *
 *      // alternatively, the `Metadata::addValues()` method can be used:
 *      $model->getMetadata()->addValues(['property1' => "some value", 'property2' => "some other value"] = "some other value";
 * ```
 *
 * @property string|null $object_model
 * @property int|null $object_id
 * @property int|null $content_id
 * @property int $sort_order
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 * @property int|null $show_in_stream
 * @property string|null $hash_sha1
 *
 * @property StorageManager $store
 * @property FileHistory[] $historyFiles
 *
 * @mixin PolymorphicRelation
 * @mixin GUID
 *
 * Following properties are optional and for module-dependent use:
 * - title
 *
 * @since 0.5
 * @noinspection PropertiesInspection
 */
class File extends FileCompat implements ViewableInterface
{
    /**
     * @event Event that is triggered after a new file content has been stored.
     */
    public const EVENT_AFTER_NEW_STORED_FILE = 'afterNewStoredFile';

    /**
     * The numeric value of the published state is not yet finalized. Use with caution and expect a change of value in later versions.
     *
     * @deprecated since 1.15
     * @since 1.15
     */
    public const STATE_PUBLISHED = 1;

    /**
     * @var int $old_updated_by
     */
    public $old_updated_by;

    /**
     * @var string $old_updated_at
     */
    public $old_updated_at;

    /**
     * @var StorageManagerInterface the storage manager
     *
     * @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
     **/
    private $_store = null;
    /* @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore */

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
            [
                ['mime_type'],
                'match',
                'not' => true,
                'pattern' => '/[^a-zA-Z0-9\.ä\/\-\+]/',
                'message' => Yii::t('FileModule.base', 'Invalid Mime-Type'),
            ],
            [['category', 'size', 'state', 'sort_order'], 'integer'],
            [['file_name', 'title'], 'string', 'max' => 255],
            [['guid'], UUIDValidator::class],
            [['guid'], 'unique'],
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
     * @return array
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function transactions()
    {
        return [
            // ToDo: enable in v.16
            // 'default' => self::OP_INSERT + self::OP_DELETE,
        ];
    }

    public function __get($name)
    {
        if ($name === 'metadata') {
            return $this->getMetadata();
        }

        return parent::__get($name);
    }

    /**
     * Gets a query for [[FileHistory]].
     *
     * @return ActiveQuery
     */
    public function getHistoryFiles()
    {
        /** @noinspection MissedFieldInspection */
        return $this->hasMany(FileHistory::class, ['file_id' => 'id'])->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        /**
         * Used for file history
         *
         * @see FileHistory::createEntryForFile()
         */
        $this->old_updated_by = $this->getOldAttribute('updated_by');
        $this->old_updated_at = $this->getOldAttribute('updated_at');

        $this->state ??= self::STATE_PUBLISHED;

        $this->sort_order ??= 0;

        $metadata = $this->getAttribute('metadata');

        if (($metadata instanceof StdClass) && !$metadata->isModified()) {
            $this->setAttribute('metadata', null);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->getStore()->delete();
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
     * @param bool $absolute
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
     * @param int $length Return number of first chars of the file hash, 0 - unlimited
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getHash($length = 0)
    {
        $store = $this->getStore();

        if (empty($this->hash_sha1) && $store->has()) {
            $this->updateAttributes(['hash_sha1' => sha1_file($store->get())]);
        }

        return $length
            ? substr($this->hash_sha1 ?: '', 0, $length)
            : $this->hash_sha1;
    }

    /**
     * @deprecated Use canView() instead. It will be deleted since v1.17
     */
    public function canRead($user = null): bool
    {
        return $this->canView($user);
    }

    /**
     * @inheritdoc
     */
    public function canView($user = null): bool
    {
        $object = $this->getPolymorphicRelation();

        if ($object instanceof ViewableInterface) {
            return $object->canView($user);
        }

        if ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord) {
            return $object->content->canView($user);
        }

        return true;
    }

    /**
     * Checks if given file can be deleted.
     *
     * If the file is not an instance of ContentActiveRecord or ContentAddonActiveRecord
     * the file is readable for all unless there is method canEdit or canDelete implemented.
     *
     * @param null $userId
     *
     * @return bool
     * @throws IntegrityException
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws \yii\base\Exception
     */
    public function canDelete($userId = null): bool
    {
        $object = $this->getPolymorphicRelation();

        // File is not bound to an object
        if ($object === null) {
            return true;
        }

        if ($object instanceof ContentAddonActiveRecord) {
            /** @var ContentAddonActiveRecord $object */
            return $object->canEdit($userId) || $object->content->canEdit($userId);
        }

        if ($object instanceof ContentActiveRecord) {
            /** @var ContentActiveRecord $object */
            return $object->content->canEdit($userId);
        }

        if ($object instanceof ActiveRecord && method_exists($object, 'canEdit')) {
            /** @var ActiveRecord $object */
            return $object->canEdit($userId);
        }

        return false;
    }

    /**
     * Checks if this file record is already attached to record.
     *
     * @return bool is whether in use or not
     */
    public function isAssigned()
    {
        return (!empty($this->object_model));
    }

    /**
     * Checks if this file is attached to the given record
     *
     * @param ActiveRecord $record
     *
     * @return bool
     */
    public function isAssignedTo(ActiveRecord $record)
    {
        return $this->object_model === PolymorphicRelation::getObjectModel($record) && $this->object_id == $record->getPrimaryKey();
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        /** @var Metadata|null $metadata */
        $metadata = $this->getAttribute('metadata');

        if ($metadata instanceof Metadata) {
            return $metadata;
        }

        $metadata = new Metadata($metadata);

        $this->setAttribute('metadata', $metadata);

        return $metadata;
    }

    /**
     * @param string|array $metadata
     *
     * @return File
     */
    public function setMetadata($metadata): File
    {
        /** @var Metadata|null $md */
        $md = $this->metadata;

        $md->addValues($metadata);

        return $this;
    }

    /**
     * Returns the StorageManager
     *
     * @return StorageManagerInterface
     * @throws InvalidConfigException
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
     * @param ActiveRecord $record
     * @return File[]
     */
    public static function findByRecord(ActiveRecord $record): array
    {
        return self::findAll(['object_model' => PolymorphicRelation::getObjectModel($record), 'object_id' => $record->getPrimaryKey()]);
    }

    /**
     * Get File History by ID
     *
     * @param int $fileHistoryId
     *
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
     *
     * @throws Exception
     * @throws IntegrityException
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     * @since 1.10
     */
    public function setStoredFile($file, $skipHistoryEntry = false)
    {
        $this->beforeNewStoredFile($skipHistoryEntry);

        $store = $this->getStore();

        if ($file instanceof UploadedFile) {
            $store->set($file);
        } elseif ($file instanceof self) {
            if ($file->isAssigned()) {
                throw new InvalidArgumentException('Already assigned File records cannot stored as another File record.');
            }
            $store->setByPath($file->getStore()->get());
            $file->delete();
        } elseif (is_string($file) && is_file($file)) {
            $store->setByPath($file);
        }

        $this->afterNewStoredFile();
    }


    /**
     * Sets a new file content by a given string.
     *
     * @param string $content
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     *
     * @throws Exception
     * @throws IntegrityException
     * @throws \yii\base\Exception
     * @since 1.10
     */
    public function setStoredFileContent($content, $skipHistoryEntry = false)
    {
        $this->beforeNewStoredFile($skipHistoryEntry);
        $this->getStore()->setContent($content);
        $this->afterNewStoredFile();
    }

    /**
     * Steps that must be executed before a new file content is set.
     *
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     *
     * @throws Exception|IntegrityException|InvalidConfigException
     */
    private function beforeNewStoredFile(bool $skipHistoryEntry)
    {
        if ($this->isNewRecord) {
            throw new Exception('File Record must be saved before setting a new file content.');
        }

        $store = $this->getStore();

        if ($store->has() && FileHistory::isEnabled($this) && !$skipHistoryEntry) {
            FileHistory::createEntryForFile($this);
        }

        $store->delete(null, ['file', FileHistory::VARIANT_PREFIX . '*']);
    }

    /**
     * Steps that must be performed after a new file content has been set.
     */
    private function afterNewStoredFile()
    {
        $store = $this->getStore();

        if ($store->has()) {
            // Make sure to update updated_by & updated_at and avoid save()
            $this->beforeSave(false);

            $filename = $store->get();
            $this->updateAttributes([
                'hash_sha1' => sha1_file($filename),
                'size' => filesize($filename),
                'updated_by' => $this->updated_by,
                'updated_at' => $this->updated_at,
            ]);
            $this->trigger(self::EVENT_AFTER_NEW_STORED_FILE);
        }
    }
}
