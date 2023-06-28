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
use humhub\interfaces\DeletableInterface;
use humhub\interfaces\EditableInterface;
use humhub\interfaces\ReadableInterface;
use humhub\interfaces\StatableInterface;
use humhub\interfaces\ViewableInterface;
use humhub\libs\StatableTrait;
use humhub\libs\StdClass;
use humhub\libs\UUID;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\file\components\StorageManager;
use humhub\modules\file\components\StorageManagerInterface;
use humhub\modules\file\exceptions\InvalidFileGuidException;
use humhub\modules\file\exceptions\MimeTypeNotSupportedException;
use humhub\modules\file\libs\FileActiveQuery;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\models\forms\FileUploadInterface;
use humhub\modules\file\models\forms\FileUploadTrait;
use humhub\modules\file\Module;
use humhub\modules\file\services\FileStateService;
use humhub\modules\file\validators\FileNameValidator;
use humhub\modules\file\validators\FileValidator;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord as DbActiveRecord;
use yii\db\Exception;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 *
 * @property integer $id
 * @property string $guid
 * @property string $file_name
 * @property string $title
 * @property string $mime_type
 * @property string $size
 * @property Metadata $metadata
 * @property string|null $object_model
 * @property integer|null $object_id
 * @property integer|null $content_id
 * @property integer $sort_order
 * @property integer|null $category
 * @property string|null $created_at
 * @property integer|null $created_by
 * @property string|null $updated_at
 * @property integer|null $updated_by
 * @property integer|null $show_in_stream
 * @property string|null $hash_sha1
 *
 * @property DbActiveRecord|null $owner
 * @property User $createdBy
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
 * @codingStandardsIgnoreFile PSR2.Methods.MethodDeclaration.Underscore
 */
class File extends FileCompat implements FileInterface, ViewableInterface
{
    use StatableTrait {
        findByCondition as protected _findByCondition;
    }

    public const CATEGORY_ATTACHED_FILE = 16;
    public const CATEGORY_ATTACHED_IMAGE = self::CATEGORY_ATTACHED_FILE + self::CATEGORY_VARIANT_1;         // 17 = 16 + 1
    public const CATEGORY_BANNER_IMAGE = self::CATEGORY_ATTACHED_FILE + self::CATEGORY_VARIANT_1 + self::CATEGORY_VARIANT_2;         // 19 = 16 + 1 + 2
    // @see https://developers.facebook.com/docs/sharing/webmasters
    public const CATEGORY_OG_IMAGE = self::CATEGORY_ATTACHED_FILE + self::CATEGORY_VARIANT_1 + self::CATEGORY_VARIANT_4;         // 21 = 16 + 1 + 4
    public const CATEGORY_RESERVED_4_NOT_IMAGE = 4;
    public const CATEGORY_RESERVED_8_NOT_IMAGE = 8;
    public const CATEGORY_VARIANT_1 = 1;
    public const CATEGORY_VARIANT_2 = 2;
    public const CATEGORY_VARIANT_4 = 4;
    public const CATEGORY_VARIANT_8 = 8;

    public const WELL_KNOWN_METADATA_IMG_ALT_TEXT = 'img.alt';
    public const WELL_KNOWN_METADATA_UPLOAD_HASH = 'file._upload.hash';
    public const WELL_KNOWN_METADATA_UPLOAD_MIMETYPE = 'file._upload.mimetype';
    public const WELL_KNOWN_METADATA_UPLOAD_SIZE = 'file._upload.size';
    public const WELL_KNOWN_METADATA_DRAFT_HASH = 'file._draft.hash';
    public const WELL_KNOWN_METADATA_DRAFT_MIMETYPE = 'file._draft.mimetype';
    public const WELL_KNOWN_METADATA_ORIGINAL_HASH = 'file._original.hash';
    public const WELL_KNOWN_METADATA_ORIGINAL_MIMETYPE = 'file._original.mimetype';
    public const WELL_KNOWN_METADATA_ORIGINAL_SIZE = 'file._original.size';

    /**
     * @event Event that is triggered after a new file content has been stored.
     */
    public const EVENT_AFTER_NEW_STORED_FILE = 'afterNewStoredFile';

    public const UPDATE_REPLACE = 0;
    public const UPDATE_DRAFT = 1;
    public const UPDATE_AS_NEW = 2;

    /**
     * @var int Flag regarding the upload work-flow
     */
    public int $updateMode = self::UPDATE_REPLACE;

    /**
     * @var int $old_updated_by
     */
    public $old_updated_by;

    /**
     * @var string $old_updated_at
     */
    public $old_updated_at;

    /**
     * @var StorageManager|string|null
     */
    public ?string $storeClass = null;


    public static string $fileUploadFieldName = 'file';

    /**
     * Comma- or space separated list or array of allowed extensions
     *
     * @var array|string|null
     * @see https://www.yiiframework.com/doc/api/2.0/yii-validators-filevalidator#$extensions-detail
     * @see FileValidator::$extensions
     * @see FileUploadTrait::rules()
     */
    public $fileUploadAllowedExtensions;

    /**
     * @var array
     * @see https://www.yiiframework.com/doc/api/2.0/yii-validators-filevalidator#properties
     * @see FileUploadTrait::rules()
     */
    public array $fileValidatorArguments = [];

    public string $urlBase = '/file/file/download';

    /**
     * @var StorageManagerInterface|null the storage manager
     */
    private ?StorageManagerInterface $_store = null;

    private ?DbActiveRecord $_owner = null;
    public ?string $uploadVariant = null;

    /**
     * @var int|null
     */
    public static ?int $defaultFilterCategory = null;

    /**
     * @var bool
     */
    public static bool $defaultFilterCategoryAsBitmask = false;

    public function __get($name)
    {
        if ($name === 'owner' && $this->_owner !== null) {
            return $this->_owner;
        }

        $value = parent::__get($name);

        if ($name === 'owner' && $value !== null) {
            $this->_owner = $value;
        }

        if ($name === 'metadata' && !$value instanceof StdClass) {
            $value = new StdClass($value);
            $this->setAttribute('metadata', $value);
        }

        return $value;
    }

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
            [['file_name', 'title'], 'string', 'max' => 255],
            [['file_name'], FileNameValidator::class],
            [['size', 'sort_order'], 'integer'],
            [['sort_order'], 'unique', 'targetAttribute' => ['sort_order', 'object_model', 'object_id']],
            [['state'], 'validateStateAttribute'],
            [['state'], 'unique', 'targetAttribute' => ['state', 'guid']],
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
            'default' => self::OP_INSERT + self::OP_DELETE,
        ];
    }

    /**
     * @param array $row = [
     *                      'id' => int,
     *                      'guid' => string,
     *                      'state' => int,
     *                      'object_model' => string,
     *                      'object_id' => int,
     *                      'sort_order' => int,
     *                      'category' => int, // category
     *                      'content_id' => int,
     *                      'file_name' => string,
     *                      'title' => string,
     *                      'mime_type' => string,
     *                      'size' => int,
     *                      'metadata' => string,
     *                      'created_at' => string,
     *                      'created_by' => int,
     *                      'updated_at',
     *                      'updated_by',
     *                      'show_in_stream' => bool,
     *                      'hash_sha1' => string,
     *                      ]
     *
     * @return FileUploadInterface|AttachedImage
     */
    public static function instantiate($row)
    {
        $category = $row['category'] ?? null;

        if (
            $category !== null && $category & self::CATEGORY_ATTACHED_IMAGE && (!is_subclass_of(
                static::class,
                AttachedImage::class
            ))
        ) {
            return new AttachedImage();
        }

        return parent::instantiate($row);
    }

    /**
     * Gets query for [[FileHistory]].
     *
     * @return ActiveQuery
     */
    public function getHistoryFiles(): ActiveQuery
    {
        return $this->hasMany(FileHistory::class, ['file_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
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

        if (($metadata instanceof StdClass) && $metadata->count() === 0) {
            $this->setAttribute('metadata', null);
        }

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
     * @param string|array $params = [
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
     * @param string|array $params = [
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

        if ($this->state !== self::STATE_PUBLISHED) {
            $params['state'] = $this->state;
        }

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
        if (empty($this->hash_sha1) && $this->store->has()) {
            $this->updateAttributes(['hash_sha1' => sha1_file($this->store->get())]);
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
     *
     * @param string|User $userId
     *
     * @return bool
     * @throws IntegrityException
     * @throws Throwable
     * @throws \yii\base\Exception
     */
    public function canRead($userId = ""): bool
    {
        if (!$this->canUnassigned()) {
            return false;
        }

        $object = $this->getPolymorphicRelation();

        if ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord) {
            return $object->content->canView($userId);
        }

        if ($object instanceof ReadableInterface) {
            return $object->canRead($userId);
        }

        if ($object instanceof ViewableInterface) {
            return $object->canView($userId);
        }

        return true;
    }

    public function canView($user = null): bool
    {
        return $this->canRead($user);
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
        if (!$this->canUnassigned()) {
            return false;
        }

        $object = $this->getPolymorphicRelation();

        if ($object instanceof ContentAddonActiveRecord) {
            /** @var ContentAddonActiveRecord $object */
            return $object->canDelete($userId) || $object->content->canEdit($userId);
        }

        if ($object instanceof ContentActiveRecord) {
            return $object->content->canEdit($userId);
        }

        if ($object instanceof DeletableInterface) {
            return $object->canDelete($userId);
        }

        if ($object instanceof EditableInterface || ($object instanceof ActiveRecord && method_exists($object,
                    'canEdit'))) {
            return $object->canEdit($userId);
        }

        return false;
    }

    /**
     * @inheritdoc
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     * @throws IntegrityException
     */
    public function canEdit($user = null): bool
    {
        if (!$this->canUnassigned()) {
            return false;
        }

        $object = $this->getPolymorphicRelation();

        if ($object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord) {
            return $object->content->canEdit($user);
        }

        if ($object instanceof EditableInterface) {
            return $object->canEdit($user);
        }

        $appUser = Yii::$app->user;

        if ($appUser->isGuest) {
            return false;
        }

        if ($user === null) {
            $user = $appUser->getIdentity();
        } elseif (!$user instanceof User && !($user = User::findOne(['id' => $user]))) {
            return false;
        }

        return $this->created_by === $user->id;
    }

    public function canUnassigned(): bool
    {
        // File is not assigned to any database record (yet)
        $appUser = Yii::$app->user;

        if (empty($this->object_model) && ($appUser->isGuest || $this->created_by !== $appUser->id)) {
            return false;
        }

        return true;
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
     * Gets query for [[FileHistory]].
     *
     * @return ActiveQuery
     */
    public function getOwner(): ?ActiveQuery
    {
        if (empty($this->object_model) || "$this->object_id" === '') {
            return null;
        }

        return $this->hasOne($this->object_model, ['id' => 'object_id']);
    }

    /**
     * @param DbActiveRecord|null $owner
     *
     * @return File
     */
    public function setOwner(?DbActiveRecord $owner): File
    {
        $this->_owner = $owner;

        return $this;
    }

    /**
     * @return StdClass
     */
    public function getMetadata(): StdClass
    {
        /** @var StdClass|null $md */
        $md = $this->getAttribute('metadata');

        if ($md instanceof StdClass) {
            return $md;
        }

        $md = new StdClass($md);

        $this->setAttribute('metadata', $md);

        return $this->metadata;
    }

    /**
     * @param string|array $metadata
     *
     * @return File
     */
    public function setMetadata($metadata): File
    {
        /** @var StdClass|null $md */
        $md = $this->metadata;

        $md->addValues($metadata);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getStateServiceClass(): string
    {
        return FileStateService::class;
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
            $this->_store = Yii::createObject($this->storeClass ?? Yii::$app->getModule('file')->storageManagerClass);
            $this->_store->setFile($this);
        }

        return $this->_store;
    }

    /**
     * @return FileActiveQuery|ActiveQuery
     */
    public static function find(): ActiveQuery
    {
        if (!Yii::$app->params['databaseInstalled']) {
            $table = Yii::$app->db->schema->getTableSchema(static::tableName(), true);
            if(!array_key_exists('state', $table->columns)) {
               return parent::find();
            }
        }

        $query = new FileActiveQuery(static::class);
        $query->category = static::$defaultFilterCategory;
        $query->categoryAsBitmask = static::$defaultFilterCategoryAsBitmask;
        return $query;
    }

    protected static function findByCondition($condition, $allowedStates = null): ActiveQuery
    {
        $owner = false;

        if (is_array($condition) && array_key_exists('owner', $condition)) {
            $owner = $condition['owner'];
            if ($owner === null || $owner instanceof ActiveRecord) {
                unset ($condition['owner']);
            } else {
                $owner = false;
            }
        }
        $query = static::_findByCondition($condition, $allowedStates);
        if ($owner !== false) {
            $query->setOwner($owner);
        }

        return $query;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function findByGuid($guid, $allowedStates = null): ?File
    {
        if (is_array($guid)) {
            $guid = $guid['guid'] ?? null;
        } elseif (is_object($guid)) {
            $guid = $guid->guid ?? null;
        }

        if (!UUID::is_valid($guid)) {
            return null;
        }

        $condition = ['guid' => $guid];

        return static::findOne($condition, $allowedStates);
    }

    /**
     * Returns all attached Files of the given $record.
     *
     * @param DbActiveRecord $record
     * @param array $condition
     *
     * @return File[]
     * @throws InvalidConfigException
     */
    public static function findByRecord(DbActiveRecord $record, $condition = []): array
    {
        $condition['object_model'] = PolymorphicRelation::getObjectModel($record);
        $condition['object_id'] = $record->getPrimaryKey();

        static::find()->setOwner($record)->all($condition);

        return self::findAll($condition);
    }

    /**
     * Returns all attached Files of the given $record.
     *
     * @param DbActiveRecord $record
     * @param array $condition
     *
     * @return File|null
     * @throws InvalidConfigException
     */
    public static function findOneByRecord(DbActiveRecord $record, $condition = []): ?File
    {
        $condition['owner'] = $record;

        return self::findOne($condition);
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
     * @param UploadedFile|FileInterface|string $file File object or path
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if
     *                                                            enabled by the record
     *
     * @return File
     * @throws ErrorException
     * @throws Exception
     * @throws IntegrityException
     * @throws Throwable
     * @throws InvalidFileGuidException
     * @throws \yii\base\Exception
     * @throws StaleObjectException
     * @since 1.10
     */
    public function setStoredFile(
        $file,
        bool $skipHistoryEntry = false
    ): self {
        $this->beforeNewStoredFile($file, $skipHistoryEntry);

        $store = $this->store;

        if ($file instanceof UploadedFile) {
            $destination = $store->set($file, $this->uploadVariant);
            $source = $file->name;
        } elseif ($file instanceof self) {
            if ($file->isAssigned()) {
                throw new InvalidArgumentException(
                    'Already assigned File records cannot stored as another File record.'
                );
            }
            $destination = $store->setByPath($file->store->get());
            $source = $file->file_name;
            $file->delete();
        } elseif (is_string($file) && is_file($file)) {
            $destination = $store->setByPath($file);
            $source = null;
        } else {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$file'],
                [UploadedFile::class, FileInterface::class, "string"],
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
    public function setStoredFileContent(
        $content,
        $skipHistoryEntry = false
    ) {
        $this->beforeNewStoredFile(null, $skipHistoryEntry);
        $destination = $this->store->setContent($content);
        $this->afterNewStoredFile($destination);
    }

    /**
     * Steps that must be executed before a new file content is set.
     *
     * @param string|UploadedFile|File|null $file
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if
     *                                                         enabled by the record
     *
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws IntegrityException
     */
    protected function beforeNewStoredFile(
        $file,
        bool $skipHistoryEntry
    ): void {
        if ($this->isNewRecord) {
            throw new Exception('File Record must be saved before setting a new file content.');
        }

        // check for valid argument
        if ($file !== null) {
            self::extractPath($file);
        }

        if ($this->updateMode === self::UPDATE_REPLACE && !$skipHistoryEntry && $this->store->has() && FileHistory::isEnabled($this)) {
            FileHistory::createEntryForFile($this);
        }

        $options = $this->updateMode === self::UPDATE_REPLACE
            ? null
            : [
                'filter' => static fn($path) => preg_match(
                    sprintf('@%1$s_draft(_[^%1$s])?$@', str_replace('\\', '\\\\', DIRECTORY_SEPARATOR)),
                    $path
                )
            ];

        $this->store->delete(null, [FileHistory::VARIANT_PREFIX . '*'], $options);
    }

    /**
     * @param $insert
     * @param $changedAttributes
     *
     * @throws ErrorException
     */
    public function afterSave(
        $insert,
        $changedAttributes
    ) {
        parent::afterSave($insert, $changedAttributes);

        /**
         *  make sure the unique index ux-file-object is not violated. Hence, if sort_order is zero (0) set it to the PK.
         *
         * @see self::updateAttributes()
         */
        $this->updateAttributes([]);
    }

    /**
     * Steps that must be performed after a new file content has been set.
     *
     * @param string|null $destination Path of destination file
     * @param string|null $source Source path, if available. May be "virtual", e.g. when file was uploaded
     *
     * @throws ErrorException
     * @throws \yii\base\Exception|\ErrorException
     */
    protected function afterNewStoredFile(?string $destination, ?string $source = null, ?array $attributes = null)
    {
        if (!($destination = $this->store->has($destination))) {
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
            $metadata->{self::WELL_KNOWN_METADATA_ORIGINAL_MIMETYPE} ??= $this->mime_type;
            $attributes['mime_type'] = $mimeType;
        }

        $attributes = array_intersect_key($attributes, array_flip($this->attributes()));

        $this->updateAttributes($attributes);
        $this->trigger(self::EVENT_AFTER_NEW_STORED_FILE);
    }

    /**
     * make sure the unique index ux-file-object is not violated. Hence, if sort_order is zero (0) set it to the PK.
     *
     * @param array|ArrayAccess $attributes
     *
     * @return void
     * @throws ErrorException
     */
    public function updateAttributes($attributes): void
    {
        if (
            (
                (is_array($attributes) && array_key_exists('sort_order', $attributes))
                || ($attributes instanceof ArrayAccess && $attributes->offsetExists('sort_order'))
            )
            && empty($attributes['sort_order'])
        ) {
            throw new ErrorException("File.sort_order cannot be set to 0 or null");
        }

        if (empty($this->sort_order)) {
            $attributes['sort_order'] ??= $this->id;
        }

        parent::updateAttributes($attributes);
    }

    protected static function extractPath(
        $file,
        bool $checkExists = true
    ): ?string {
        switch (true) {
            case is_string($file):
                $path = $file;
                break;

            case $file instanceof UploadedFile:
                $path = $file->tempName;
                break;

            case $file instanceof self:
                $path = $file->store->has('_original') ?? $file->store->has();
                break;

            case $file === null:
                $path = null;
                break;

            default:
                throw new InvalidArgumentTypeException(
                    __METHOD__,
                    [0 => '$file'],
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

    public static function sanitizeFilename(
        string $filename,
        ?int &$countPatternSubstitutions = null,
        ?int &$countExtensionSubstitutions = null
    ): ?string {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        $pattern = $module->fileNameValidationPattern;

        if (empty($pattern)) {
            return null;
        }

        $changed = false;

        if ($module->denyDoubleFileExtensions) {
            $filename = preg_replace('/\.(\w{2,3}\.\w{2,3})$/', '_$1', $filename, -1, $countExtensionSubstitutions);
            $changed = $countExtensionSubstitutions > 0;
        }

        $sanitized = preg_replace($pattern, '_', $filename, -1, $countPatternSubstitutions);

        $changed |= $countPatternSubstitutions > 0;

        return $changed
            ? $sanitized
            : null;
    }
}
