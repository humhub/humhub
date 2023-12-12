<?php

/*
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

    protected function formatPrologue(): string
    {
        return "Parameter '$this->parameter' of configuration";
    }

    protected function formatValid(): string
    {
        if (empty($this->valid)) {
            $this->valid = ['mixed'];
        }

        return (count($this->valid) > 1
                ? 'one of the following type '
                : 'of type ') . implode(', ', $this->valid);
    }

    protected function formatGiven(): string
    {
        return get_debug_type($this->given);
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return parent::getName() . " Type";
        }

        return 'Invalid Type';
    }
}
