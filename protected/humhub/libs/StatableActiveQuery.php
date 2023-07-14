<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableActiveQueryInterface;
use yii\db\ActiveQuery;

/**
 * @noinspection MissingActiveRecordInActiveQueryInspection
 */


class StatableActiveQuery extends ActiveQuery implements StatableActiveQueryInterface
{
    use StatableActiveQueryTrait;
}
