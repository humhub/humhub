<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use ArrayAccess;
use humhub\components\ActiveRecord;
use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\libs\StdClass;
use humhub\libs\UUID;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\file\components\StorageManager;
use humhub\modules\file\components\StorageManagerInterface;
use humhub\modules\file\exceptions\InvalidFileGuidException;
use humhub\modules\file\exceptions\MimeTypeNotSupportedException;
use humhub\modules\file\exceptions\MimeTypeUnknownException;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\libs\Metadata;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;
use yii\db\StaleObjectException;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * The following are the available columns in table 'file':
 *
 * @property integer $id
 * @property string $guid
 * @property integer $state
 * @property integer|null $category Note, categories are still experimental. Expect changes in v1.16 (ToDo)
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
 * @property integer|null $object_id
 * @property integer|null $content_id
 * @property integer $sort_order
 * @property string|null $created_at
 * @property integer|null $created_by
 * @property string|null $updated_at
 * @property integer|null $updated_by
 * @property integer|null $show_in_stream
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
class File extends FileCompat
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
     * @var StorageManagerInterface|StorageManager|null the storage manager
     */
    private ?StorageManagerInterface $store = null;

    /**
     * @var StorageManagerInterface|StorageManager|string|null
     */
    public ?string $storeClass = null;

    public string $urlBase = '/file/file/download';

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
                'message' => Yii::t('FileModule.base', 'Invalid Mime-Type')
            ],
            [['category', 'size', 'state', 'sort_order'], 'integer'],
            [['file_name', 'title'], 'string', 'max' => 255],
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
     * @param null|string|array $params = [
     *     'variant' => string      // the requested file variant
     *     'download' => bool       // force download option (default: false)
     * ]
     * @param boolean $absolute
     *
     * @return string the url to the file download
     */
    public function getUrl($params = [], bool $absolute = true)
    {
        return Url::to($this->getUrlParameters($params, $this->urlBase), $absolute);
    }

    /**
     * @param null|string|array $params = [
     *     'variant' => string,
     * ]
     * @param string|null $baseUrl
     *
     * @return array
     */
    public function getUrlParameters($params, ?string $baseUrl = null): array
    {
        // Handle old 'suffix' attribute for HumHub prior 1.1 versions
        if (is_string($params)) {
            $variant = $params;
            $params = [];
            if ($variant !== '') {
                $params['variant'] = $variant;
            }
        }

        $params['guid'] = $this->guid;
        $params['hash_sha1'] = $this->getHash(8);

        if ($baseUrl !== null) {
            array_unshift($params, $baseUrl);
        }

        return $params;
    }

    /**
     * Get hash
     *
     * @param int $length Return number of first chars of the file hash, 0 - unlimited
     *
     * @return string
     * @throws ErrorException
     * @throws InvalidFileGuidException
     * @throws \yii\base\Exception
     */
    public function getHash($length = 0)
    {
        $store = $this->getStore();

        if (empty($this->hash_sha1) && $store->has()) {
            $this->updateAttributes(['hash_sha1' => sha1_file($store->get())]);
        }

        return $length
            ? substr($this->hash_sha1, 0, $length)
            : $this->hash_sha1;
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
        if ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord) {
            return $object->content->canView($userId);
        }

        return true;
    }

    /**
     * Checks if given file can be deleted.
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
     * @return boolean is whether in use or not
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
        return $this->object_model === get_class($record) && $this->object_id == $record->getPrimaryKey();
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
     * @return StorageManagerInterface|StorageManager
     * @throws InvalidConfigException
     */
    public function getStore(): StorageManagerInterface
    {
        if ($this->store === null) {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->store = Yii::createObject($this->storeClass ?? Yii::$app->getModule('file')->storageManagerClass);
            $this->store->setFile($this);
        }

        return $this->store;
    }

    /**
     * @noinspection PhpUnused
     */
    public static function findByGuid($guid): ?File
    {
        if (is_array($guid)) {
            $guid = $guid['guid'] ?? null;
        } elseif (is_object($guid)) {
            $guid = $guid->guid ?? null;
        }

        $guid = UUID::validate($guid);
        if ($guid === null) {
            return null;
        }

        $condition = ['guid' => $guid];

        return static::findOne($condition);
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
    public function setStoredFile($file, bool $skipHistoryEntry = false): self
    {
        $this->beforeNewStoredFile($file, $skipHistoryEntry);

        $store = $this->getStore();

        if ($file instanceof UploadedFile) {
            $destination = $store->set($file);
            $source = $file->name;
        } elseif ($file instanceof self) {
            if ($file->isAssigned()) {
                throw new InvalidArgumentException(
                    'Already assigned File records cannot stored as another File record.'
                );
            }
            $destination = $store->setByPath($file->getStore()->get());
            $source = $file->file_name;
            $file->delete();
        } elseif (is_string($file) && is_file($file)) {
            $destination = $store->setByPath($file);
            $source = null;
        } else {
            throw new InvalidArgumentTypeException(
                '$file',
                [UploadedFile::class, self::class, "string"],
                $file
            );
        }

        $this->afterNewStoredFile($destination, $source);

        return $this;
    }


    /**
     * Sets a new file content by a given string.
     *
     * @param string $content
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     *
     * @throws ErrorException
     * @throws Exception
     * @throws IntegrityException
     * @throws \yii\base\Exception
     * @since 1.10
     */
    public function setStoredFileContent($content, $skipHistoryEntry = false)
    {
        $this->beforeNewStoredFile(null, $skipHistoryEntry);
        $destination = $this->getStore()->setContent($content);
        $this->afterNewStoredFile($destination);
    }

    /**
     * Steps that must be executed before a new file content is set.
     *
     * @param string|UploadedFile|File|null $file
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     *
     * @throws Exception|\yii\base\Exception|IntegrityException
     * @throws Exception|IntegrityException|InvalidConfigException
     */
    protected function beforeNewStoredFile($file, bool $skipHistoryEntry): void
    {
        if ($this->isNewRecord) {
            throw new Exception('File Record must be saved before setting a new file content.');
        }

        // check for valid argument
        if ($file !== null) {
            self::extractPath($file);
        }

        $store = $this->getStore();

        if (!$skipHistoryEntry && $store->has() && FileHistory::isEnabled($this)) {
            FileHistory::createEntryForFile($this);
        }

        $store->delete(null, [FileHistory::VARIANT_PREFIX . '*']);
    }

    /**
     * Steps that must be performed after a new file content has been set.
     *
     * @param string|null $destination Path of destination file
     * @param string|null $source Source path, if available. May be "virtual", e.g. when file was uploaded
     * @param array|null $attributes
     *
     * @throws InvalidConfigException when the `fileinfo` PHP extension is not installed and `$checkExtension` is `false`.
     * @throws InvalidArgumentTypeException when $config['source'] is not `null` and not a string
     * @throws MimeTypeNotSupportedException when the given mime-type cannot be converted, i.e., the mime-type does not starte with "image/"
     * @throws MimeTypeUnknownException when the mime-type starts with "image/" but is unknown/not implemented
     * @throws InvalidFileGuidException when the File::$guid property is empty
     * @throws \yii\base\Exception when the directory for the file could not be created
     */
    protected function afterNewStoredFile(?string $destination, ?string $source = null, ?array $attributes = null)
    {
        $destination = $this->getStore()->has($destination);

        if (!$destination) {
            return;
        }

        // Make sure to update updated_by & updated_at and avoid save()
        $this->beforeSave(false);

        $attributes ??= [];

        $mimeType = $attributes['mime_type'] ?? $attributes['mimeType'] ?? null;

        try {
            $mimeType ??= FileHelper::getMimeType($destination, null, false);
        } catch (InvalidConfigException $e) {
        }
        $mimeType ??= FileHelper::getMimeTypeByExtension($source);

        if (ImageHelper::class . '::downscaleImage' !== ($attributes['resultType'] ?? '')) {
            try {
                $result = ImageHelper::downscaleImage(
                    $this,
                    ['destination' => $destination, 'mimeType' => $mimeType, 'updateAttributes' => false, 'failOnError' => true]
                );

                ArrayHelper::merge($attributes, $result);
            } catch (MimeTypeNotSupportedException $e) {
                // skip downscaling for non-images
            }
        }

        $attributes['hash_sha1'] ??= sha1_file($destination);
        $attributes['size'] ??= filesize($destination);
        $attributes['updated_by'] ??= $this->updated_by;
        $attributes['updated_at'] ??= $this->updated_at;
        $attributes['metadata'] = $metadata = $this->metadata;

        if ($mimeType && $this->mime_type !== $mimeType) {
            $metadata->file->_original->mimeType ??= $this->mime_type;
            $attributes['mime_type'] = $mimeType;
        }

        $attributes = array_intersect_key($attributes, array_flip($this->attributes()));

        $this->updateAttributes($attributes);
        $this->trigger(self::EVENT_AFTER_NEW_STORED_FILE);
    }

    protected static function extractPath($file, bool $checkExists = true): ?string
    {
        switch (true) {
            case is_string($file):
                $path = $file;
                break;

            case $file instanceof UploadedFile:
                $path = $file->tempName;
                break;

            case $file instanceof self:
                $store = $file->getStore();
                $path = $store->has('_original') ?? $store->has();
                break;

            case $file === null:
                $path = null;
                break;

            default:
                throw new InvalidArgumentTypeException(
                    '$file',
                    ['string', UploadedFile::class, self::class],
                    $file
                );
        }

        if ($checkExists && !file_exists($path)) {
            throw new InvalidArgumentException("File does not exist at path $path");
        }

        if ($checkExists && !is_file($path)) {
            throw new InvalidArgumentException("Path $path is not a regular file");
        }

        return $path;
    }
}
