<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\components\ActiveRecord;
use humhub\modules\user\models\User;


/**
 * This is the model class for table "file_history".
 *
 * @property int $id
 * @property int $file_id
 * @property int $size
 * @property string $hash_sha1
 * @property string|null $created_at
 * @property int|null $created_by
 *
 * @property File $file
 * @property User $createdBy
 *
 * @since 1.10
 */
class FileHistory extends ActiveRecord
{
    const VARIANT_PREFIX = 'file-history-';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            /*
            [['file_id', 'size', 'hash_sha1'], 'required'],
            [['file_id', 'size'], 'integer'],
            [['hash_sha1'], 'string', 'max' => 32],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            */
        ];
    }

    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getFileStorePath()
    {
        return $this->file->store->get(static::VARIANT_PREFIX . $this->id);
    }


    /**
     * Checks if the file histories has been enabled for a given file.
     *
     * @param File $file
     * @return bool
     * @throws \yii\db\IntegrityException
     */
    public static function isEnabled(File $file)
    {
        if (!$file->isNewRecord && $file->isAssigned()) {

            /** @var ActiveRecord $record */
            $record = $file->getPolymorphicRelation();

            if ($record->fileManagerEnableHistory) {
                return true;
            }
        }
        return false;
    }

    /**
     * Copies the current contents of a file to the history.
     *
     * @param File $file
     * @return bool
     */
    public static function createEntryForFile(File $file)
    {
        $entry = new static;
        $entry->file_id = $file->id;
        if ($file->store->has()) {
            $entry->hash_sha1 = sha1_file($file->store->get());
            $entry->size = filesize($file->store->get());
        }
        if ($entry->save()) {
            $file->store->setByPath(
                $file->store->get(),
                static::VARIANT_PREFIX . $entry->id
            );
        }
    }


}
