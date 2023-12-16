<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Stringable;

/**
 * @since 1.16
 */
class DataTypeHelper
{
    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `int`.
     * - if False, a conversion to `int` is attempted.
     * ``
     *
     * @since 1.16
     */
    public static function filterBool($value, bool $strict = false): ?bool
    {
        // check if strict
        if (($strict && !is_bool($value)) || !is_scalar($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     *  ``
     *  - if True, `$value` must already be of type `float`.
     *  - if False, a conversion to `float` is attempted.
     *  ``
     *
     * @since 1.16
     */
    public static function filterFloat($value, bool $strict = false): ?float
    {
        // check if strict
        if (($strict && !is_float($value)) || !is_scalar($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `int`.
     * - if False, a conversion to `int` is attempted.
     * ``
     *
     * @since 1.16
     */
    public static function filterInt($value, bool $strict = false): ?int
    {
        // check if strict
        if (($strict && !is_int($value)) || !is_scalar($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `string`.
     * - if False, a conversion to `string` is attempted.
     * ``
     *
     * @since 1.16
     */
    public static function filterString($value, bool $strict = false): ?string
    {

        if ($strict) {
            return is_string($value) ? $value : null;
        }

        if ($value instanceof Stringable || (\is_object($value) && \method_exists($value, '__toString'))) {
            return $value->__toString();
        }

        if (!is_scalar($value)) {
            return null;
        }

        return (string)$value;
    }
}
