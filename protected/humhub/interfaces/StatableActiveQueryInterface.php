<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use yii\db\ActiveQueryInterface;

interface StatableActiveQueryInterface extends ActiveQueryInterface, StatableQueryInterface
{

}
