<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\behaviors;

use humhub\modules\file\interfaces\AttachedFileVersioningSupport;
use humhub\modules\file\models\File;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\base\Behavior;

/**
 * Provides File Versioning Support to the File Model
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 1.10
 */
class VersioningSupport extends Behavior
{
    /**
     * @var File
     */
    public $owner;

    /**
     * Sets a new file version.
     * This file will be the new latest version of the file.
     *
     * @internal
     * @param int $newFileId
     * @return bool
     */
    public function setNewCurrentVersion(int $newFileId): bool
    {
        if (!$this->isVersioningEnabled()) {
            throw new InvalidArgumentException("Versioning is not supported by underlying object.");
        }

        $polymorphicObject = $this->owner->getPolymorphicRelation();

        /* @var File $newVersionFile */
        $newVersionFile = File::find()
            ->where(['id' => $newFileId])
            ->andWhere(['OR',
                ['AND', ['object_model' => File::class, 'object_id' => $this->owner->id]],
                ['AND', ['object_model' => get_class($polymorphicObject), 'object_id' => $polymorphicObject->id]]
            ])
            ->one();

        if (!$newVersionFile) {
            throw new InvalidArgumentException("Could find the new file version by id.");
        }

        $newVersionFile->object_model = $this->owner->object_model;
        $newVersionFile->object_id = $this->owner->object_id;
        if (!$newVersionFile->save()) {
            return false;
        }

        return $this->updateVersions($newVersionFile, $this->owner->id);
    }

    /**
     * Update all old versions to new File
     *
     * @param File $newVersionFile File that must be current version
     * @param int $previousVersionFileId File ID of the previous version
     * @return bool
     */
    private function updateVersions(File $newVersionFile, int $previousVersionFileId = 0): bool
    {
        if (empty($newVersionFile->id) || empty($newVersionFile->object_model) || empty($newVersionFile->object_id)) {
            return false;
        }

        File::updateAll([
            'object_model' => File::class,
            'object_id' => $newVersionFile->id,
        ], [
            'or',
            ['and',
                ['object_model' => $newVersionFile->object_model],
                ['object_id' => $newVersionFile->object_id],
                ['!=', 'id', $newVersionFile->id],
            ],
            ['and',
                ['object_model' => File::class],
                ['object_id' => $previousVersionFileId],
            ]
        ]);

        return true;
    }

    /**
     * @return ActiveQuery
     */
    public function getVersionsQuery(): ActiveQuery
    {
        return File::find()
            ->addSelect(['*', 'IF(file.id = ' . $this->owner->id . ', 1, 0) AS isCurrentVersion'])
            ->where(['file.id' => $this->owner->id])
            ->orWhere([
                'file.object_model' => File::class,
                'file.object_id' => $this->owner->id
            ])
            ->orderBy([
                'isCurrentVersion' => SORT_DESC,
                'file.id' => SORT_DESC,
            ]);
    }


    /**
     * Check if the given file is a version
     *
     * @param File $versionFile
     * @return bool
     */
    public function isVersion(File $versionFile): bool
    {
        return ($versionFile->object_id == $this->owner->id && $versionFile->object_model === File::class);
    }


    /**
     * Check if the file owner object supports File Versioning
     *
     * @return bool
     */
    public function isVersioningEnabled(): bool
    {
        return ($this->owner->getPolymorphicRelation() instanceof AttachedFileVersioningSupport);
    }

    /**
     * Get current version of this File
     *
     * @return File
     */
    public function getCurrentVersion(): File
    {
        if ($this->owner->object_model === File::class) {
            $currentVersion = File::findOne($this->owner->object_id);
        }

        if (empty($currentVersion)) {
            $currentVersion = clone $this->owner;
        }

        return $currentVersion;
    }
}
