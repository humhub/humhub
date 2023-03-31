<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\models\ClassMap;

class ContentTypeStreamFilter extends StreamQueryFilter
{
    public const CATEGORY_INCLUDES = 'includes';
    public const CATEGORY_EXCLUDES = 'excludes';

    public $includes;

    public $excludes;

    public function init()
    {
        $this->includes = $this->streamQuery->includes;
        $this->excludes = $this->streamQuery->excludes;
        parent::init();

        // Sync the query includes with our filter includes
        $this->streamQuery->includes = $this->includes;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['includes', 'excludes'], 'safe']
        ];
    }

    public function apply()
    {
        if (!empty($this->includes)) {
            if (is_string($this->includes)) {
                $this->includes = [$this->includes];
            }

            if (count($this->includes) === 1) {
                $this->query->andWhere(["content.object_class_id" => ClassMap::getIdByName($this->includes[0])]);
            } elseif (!empty($this->includes)) {
                $this->query->andWhere(['IN', 'content.object_class_id', ClassMap::getIdByManyNames($this->includes)]);
            }
        }

        if (!empty($this->excludes)) {
            if (is_string($this->excludes)) {
                $this->excludes = [$this->excludes];
            }

            if (count($this->excludes) === 1) {
                $this->query->andWhere(['!=', "content.object_class_id" => ClassMap::getIdByName($this->includes[0])]);
            } elseif (!empty($this->excludes)) {
                $this->query->andWhere(['NOT IN', 'content.object_class_id', ClassMap::getIdByManyNames($this->excludes)]);
            }
        }
    }
}
