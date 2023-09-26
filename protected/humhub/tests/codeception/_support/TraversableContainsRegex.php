<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

declare(strict_types=1);

namespace tests\codeception\_support;

use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Exception;
use PHPUnit\Util\RegularExpression as RegularExpressionUtil;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TraversableContainsRegex extends TraversableContains
{
    public function toString(): string
    {
        return sprintf(
            'matches PCRE pattern "%s"',
            $this->value(),
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        $value = $this->value();

        foreach ($other as $element) {
            while (!is_string($element)) {
                if ($element instanceof \Stringable || (is_object($element) && !method_exists($element, '__toString'))) {
                    $element = $element->__toString();
                    break;
                }

                continue 2;
            }

            $match = RegularExpressionUtil::safeMatch($value, $element);

            if ($match === false) {
                throw new Exception(
                    "Invalid regex given: '{$value}'",
                );
            }

            return $match === 1;
        }

        return false;
    }
}
