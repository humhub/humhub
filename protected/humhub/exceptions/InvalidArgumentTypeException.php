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
class InvalidArgumentTypeException extends BaseInvalidArgumentException
{
    use InvalidArgumentExceptionTrait {
        getName as protected InvalidArgumentExceptionTrait_getName;
        formatGiven as protected InvalidArgumentExceptionTrait_formatGiven;
        formatValid as protected InvalidArgumentExceptionTrait_formatValid;
    }

    protected function formatValid(): string
    {
        if (empty($this->valid)) {
            $this->valid = ['mixed'];
        }

        return (count($this->valid) > 1
                ? 'one of the following types: '
                : 'of type ') . implode(', ', $this->valid);
    }

    protected function formatGiven(): string
    {
        return $this->given === null ? 'NULL' : get_debug_type($this->given);
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return $this->InvalidArgumentExceptionTrait_getName() . " Type";
        }

        return 'Invalid Type';
    }
}
