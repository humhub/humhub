<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\StatableActiveQueryInterface;
use yii\db\ActiveQuery;

class StatableActiveQuery extends CacheableActiveQuery implements StatableActiveQueryInterface
{
    use StatableActiveQueryTrait;
}
