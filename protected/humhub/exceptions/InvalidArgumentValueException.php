<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use yii\base\InvalidArgumentException as BaseInvalidArgumentException;

/**
 * @since 1.15
 */
class InvalidArgumentValueException extends BaseInvalidArgumentException
{
    use InvalidArgumentExceptionTrait;
}
