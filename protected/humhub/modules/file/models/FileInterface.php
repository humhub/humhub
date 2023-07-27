<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\interfaces\DeletableInterface;
use humhub\interfaces\EditableInterface;
use humhub\interfaces\ReadableInterface;
use humhub\interfaces\StatableInterface;
use humhub\modules\file\components\StorageManager;
use humhub\modules\file\components\StorageManagerInterface;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord as DbActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 *
 * @property integer             $id
 * @property string              $guid
 * @property string              $file_name
 * @property string              $title
 * @property string              $mime_type
 * @property string              $size
 * @property string              $metadata
 * @property string|null         $object_model
 * @property integer|null        $object_id
 * @property integer|null        $content_id
 * @property integer             $sorting
 * @property integer|null        $category
 * @property string|null         $created_at
 * @property integer|null        $created_by
 * @property string|null         $updated_at
 * @property integer|null        $updated_by
 * @property integer|null        $show_in_stream
 * @property string|null         $hash_sha1
 *
 * @property DbActiveRecord|null $owner
 * @property User                $createdBy
 * @property StorageManager      $store
 * @property FileHistory[]       $historyFiles
 *
 * @mixin PolymorphicRelation
 * @mixin GUID
 *
 * Following properties are optional and for module-dependent use:
 * - title
 *
 * @since 0.5
 */
interface FileInterface extends ReadableInterface, DeletableInterface, StatableInterface, EditableInterface
{
    /**
     * Get File History by ID
     *
     * @param int $fileHistoryId
     *
     * @return FileHistory|null
     */
    public function getFileHistoryById($fileHistoryId): ?FileHistory;

    /**
     * Get hash
     *
     * @param int Return number of first chars of the file hash, 0 - unlimit
     *
     * @return string
     */
    public function getHash($length = 0);

    /**
     * Gets query for [[FileHistory]].
     *
     * @return ActiveQuery
     */
    public function getHistoryFiles(): ActiveQuery;

    /**
     * Gets query for [[FileHistory]].
     *
     * @return ActiveQuery
     */
    public function getOwner(): ?ActiveQuery;

    /**
     * Returns the StorageManager
     *
     * @return StorageManagerInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function getStore();

    /**
     * Returns the url to this file
     *
     * Available params (see also: DownloadAction)
     * - variant: the requested file variant
     * - download: force download option (default: false)
     *
     * @param array|string $params the params
     * @param boolean      $absolute
     *
     * @return string the url to the file download
     */
    public function getUrl(
        $params = [],
        bool $absolute = true
    );

    /**
     * Checks if this file record is already attached to record.
     *
     * @return boolean is whether in use or not
     */
    public function isAssigned();

    /**
     * Checks if this file is attached to the given record
     *
     * @param ActiveRecord $record
     *
     * @return bool
     */
    public function isAssignedTo(ActiveRecord $record);

    /**
     * @param DbActiveRecord|null $owner
     *
     * @return File
     */
    public function setOwner(?DbActiveRecord $owner): File;

    /**
     * Sets a new file content based on an UploadedFile, new File or a file path.
     *
     * @param UploadedFile|File|string $file             File object or path
     * @param bool $skipHistoryEntry Skipping the creation of a history entry, even if enabled by
     *                                                   the record
     *
     * @since 1.10
     */
    public function setStoredFile(
        $file,
        bool $skipHistoryEntry = false
    );

    /**
     * Sets a new file content by a given string.
     *
     * @param string $content
     * @param bool   $skipHistoryEntry Skipping the creation of a history entry, even if enabled by the record
     *
     * @since 1.10
     */
    public function setStoredFileContent(
        $content,
        $skipHistoryEntry = false
    );

    /**
     * Checks if given file can be deleted.
     *
     * If the file is not an instance of ContentActiveRecord or ContentAddonActiveRecord
     * the file is readable for all unless there is method canEdit or canDelete implemented.
     */
    public function canDelete($userId = null);

    /**
     * Checks if given file can be read.
     *
     * If the file is not an instance of HActiveRecordContent or HActiveRecordContentAddon
     * the file is readable for all.
     *
     * @param string|User $userId
     *
     * @return bool
     */
    public function canRead($userId = ""): bool;
}
