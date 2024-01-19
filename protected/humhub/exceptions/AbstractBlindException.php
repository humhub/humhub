<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\exceptions;

use Exception;

/**
 * @since 1.16
 */
class AbstractBlindException extends Exception
{
    public function __toString(): string
    {
        return '';
    }

    public static function create(...$args): self
    {
        return new static(...$args);
    }
}
