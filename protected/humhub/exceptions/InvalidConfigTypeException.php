<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use yii\base\InvalidConfigException;

/**
 * @since 1.15
 */
class InvalidConfigTypeException extends InvalidConfigException
{
    use InvalidArgumentExceptionTrait;

    protected function formatPrologue(array $constructArguments): string
    {
        return "Parameter $this->parameter of configuration";
    }
}
