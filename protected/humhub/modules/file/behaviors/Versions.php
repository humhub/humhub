<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\behaviors;

use humhub\modules\file\models\File;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\base\Behavior;

/**
 * File Version Behavior
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 1.10
 */
class Versions extends Behavior
{
    /**
     * @var File
     */
    public $owner;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'refreshVersions',
        ];
    }

    /**
     * Relink all old versions to new inserted File
     */
    public function refreshVersions()
    {
        $previousVersionFileId = (int)File::find()
            ->select('id')
            ->where(['object_model' => $this->owner->object_model])
            ->andWhere(['object_id' => $this->owner->object_id])
            ->andWhere(['!=', 'id', $this->owner->id])
            ->scalar();

        $this->updateVersions($this->owner, $previousVersionFileId);
    }

    /**
     * Switch version of this File
     *
     * @param int $newVersionFileId
     * @return bool
     */
    public function switchVersion(int $newVersionFileId): bool
    {
        /* @var File $newVersionFile */
        $newVersionFile = File::find()
            ->where(['id' => $newVersionFileId])
            ->andWhere(['object_model' => File::class])
            ->andWhere(['object_id' => $this->owner->id])
            ->one();

        if (!$newVersionFile) {
            return false;
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
        return (bool)File::updateAll([
            'object_model' => File::class,
            'object_id' => $newVersionFile->id,
        ], [
            'or',
            [   'and',
                ['object_model' => $newVersionFile->object_model],
                ['object_id' => $newVersionFile->object_id],
                ['!=', 'id', $newVersionFile->id],
            ],
            [   'and',
                ['object_model' => File::class],
                ['object_id' => $previousVersionFileId],
            ]
        ]);
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
     * Get all versions including current
     *
     * @return File[]
     */
    public function getVersions(): array
    {
        return $this->getVersionsQuery()->all();
    }

    /**
     * Check if the requested file is a version of the object
     *
     * @param string $objectClassName
     * @param int $versionFileId
     * @return bool
     */
    public function isVersion(string $objectClassName, int $versionFileId): bool
    {
        return File::find()
            ->innerJoin(File::tableName() . ' as currentVersion', 'currentVersion.id = file.object_id')
            ->where(['file.id' => $versionFileId])
            ->andWhere(['currentVersion.object_model' => $objectClassName])
            ->exists();
    }
}
