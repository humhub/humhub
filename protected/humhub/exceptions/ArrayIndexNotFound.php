<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace exceptions;

use humhub\exceptions\AbstractBlindException;

/**
 * @since 1.15
 */
class ArrayIndexNotFound extends AbstractBlindException
{
    /**
     * @var int|string|null
     */
    public $index;

    /**
     * @param int|string|null $index
     *
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct($index = null)
    {
        $this->index = $index;
    }

    public function __toString(): string
    {
        return  $this->index;
    }

    /**
     * @return int|string|null
     */
    public function getIndex()
    {
        return $this->index;
    }
}
