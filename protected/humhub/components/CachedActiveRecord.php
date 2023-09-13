<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\FindInstanceInterface;

/**
 * @since 1.15
 */
abstract class CachedActiveRecord extends ActiveRecord implements FindInstanceInterface
{
    use FindInstanceTrait;
}
