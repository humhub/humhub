<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\libs\StatableActiveQuery;
use humhub\modules\file\models\File;
use yii\db\ActiveRecord;

class FileActiveQuery extends StatableActiveQuery
{
    //  public properties
    public ?ActiveRecord $owner = null;

    public ?int $category = null;
    public bool $categoryAsBitmask = false;

    /**
     * @param ActiveRecord|null $owner
     *
     * @return FileActiveQuery
     */
    public function setOwner(?ActiveRecord $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    protected function createModels($rows): array
    {
        if ($this->asArray || $this->owner === null) {
            return parent::createModels($rows);
        }

        $models = parent::createModels($rows);
        $owner = $this->owner;

        array_walk($models, static function (File $file) use ($owner) {
            $file->setOwner($owner);
        });

        return $models;
    }

    public function createCommand($db = null)
    {
        if ($this->owner) {
            $this->andWhere(['object_model' => get_class($this->owner), 'object_id' => $this->owner->primaryKey]);
        }

        if ($this->category !== null) {
            if ($this->categoryAsBitmask) {
                $this->andWhere('category & :category = :category', ['category' => $this->category]);
            } else {
                $this->andWhere(['category' => $this->category]);
            }
        }

        return parent::createCommand($db);
    }
}
