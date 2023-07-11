<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

class ArrayValueFound extends AbstractBlindException
{
    /**
     * @var int|string|null
     */
    public $index;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @param mixed           $value
     * @param int|string|null $index
     *
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        &$value,
        $index = null
    ) {
        $this->value = &$value;
        $this->index = $index;
    }

    public function __toString(): string
    {
        return get_debug_type($this->value);
    }

    /**
     * @return int|string|null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
