<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use yii\base\InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException
{
    use InvalidArgumentExceptionTrait;

    protected function formatPrologue(array $constructArguments): string
    {
        $argumentName = is_array($this->parameter)
            ? reset($this->parameter)
            : null;

        $argumentNumber = is_array($this->parameter)
            ? key($this->parameter)
            : $this->parameter;

        if (null === $int = filter_var($argumentNumber, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            $argumentName = $argumentNumber;
            $argumentNumber = '';
        } else {
            $argumentNumber = "#$int";
        }

        $argumentName = $argumentName === null
            ? ''
            : " \$" . ltrim($argumentName, '$');

        return sprintf('Argument %s%s', $argumentNumber, $argumentName);
    }
}
