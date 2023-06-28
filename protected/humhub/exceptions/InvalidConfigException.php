<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

class InvalidConfigException extends \yii\base\InvalidConfigException
{
    use InvalidArgumentExceptionTrait;

    protected function formatPrologue(array $constructArguments): string
    {
        return "Parameter $this->parameter of configuration";
    }
}
