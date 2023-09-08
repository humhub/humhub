<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\helpers\RuntimeCacheHelper;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * ActiveQueryContent is an enhanced ActiveQuery with additional selectors for especially content.
 *
 * @inheritdoc
 */
class CacheableActiveQuery extends ActiveQuery
{
    public function findFor($name, $model)
    {
        $result = parent::findFor($name, $model);

        if (!$result instanceof ActiveRecord) {
            return $result;
        }

        return RuntimeCacheHelper::setVariants($result);
    }
}
