<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

/**
 * What {@see ListFilter::parse()} made of a filter's request parameters: a typed value the
 * caller sent (`present`), nothing (`absent` — the filter stays dormant and is not applied), or
 * the errors of an invalid value, by parameter.
 *
 * @since 1.20
 */
final class FilterValue
{
    /**
     * @param array<string, string[]> $errors
     */
    private function __construct(
        public readonly bool $present,
        public readonly mixed $value = null,
        public readonly array $errors = [],
    ) {
    }

    /**
     * The caller did not send the filter (or sent it empty).
     */
    public static function absent(): self
    {
        return new self(false);
    }

    /**
     * A valid value — a string, an int, a bool, a list of them, whatever the filter's type is.
     */
    public static function of(mixed $value): self
    {
        return new self(true, $value);
    }

    /**
     * @param array<string, string[]> $errors the messages by request parameter, the way they
     *        reach the `422` answer
     */
    public static function invalid(array $errors): self
    {
        return new self(false, null, $errors);
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
