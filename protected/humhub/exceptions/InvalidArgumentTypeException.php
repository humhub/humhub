<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use yii\base\InvalidArgumentException;

/**
 * @since 1.15
 */
class InvalidArgumentTypeException extends InvalidArgumentException
{
    use InvalidTypeExceptionTrait;

    protected function formatPrologue(array $constructArguments): string
    {
        $argumentName = is_array($this->parameter)
            ? reset($this->parameter)
            : null;
        $argumentNumber = is_array($this->parameter)
            ? key($this->parameter)
            : $this->parameter;

        $argumentName = $argumentName === null
            ? ''
            : " \$" . ltrim($argumentName, '$');

        return sprintf('Argument #%d%s', $argumentNumber, $argumentName);
    }
}
