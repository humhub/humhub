<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

/**
 * @since 1.15
 */
class InvalidArgumentClassException extends InvalidArgumentTypeException
{
    protected function formatValid(): string
    {
        if (empty($this->valid)) {
            $this->valid = ['mixed'];
        }

        if (count($this->valid) > 1) {
            return 'one of the following types: ' . implode(', ', $this->valid);
        }

        if (strpos($this->valid[0], ' ') === false) {
            return 'of type ' . reset($this->valid);
        }

        return reset($this->valid);
    }

    protected function formatGiven(): string
    {
        $given = parent::formatGiven();

        if ($given === 'string') {
            $given = $this->InvalidArgumentExceptionTrait_formatGiven();
        }

        return $given;
    }

    public function getName(): string
    {
        if (method_exists(parent::class, 'getName')) {
            return parent::getName() . " Type";
        }

        return 'Invalid Type';
    }
}
