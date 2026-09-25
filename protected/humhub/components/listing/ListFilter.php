<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use Yii;

/**
 * One filter of a {@see FilterableList}, knowing everything about itself: its request
 * parameters, how to parse and validate them, whether it is available in a context, how it
 * narrows the list and how it is presented. The HTTP API validates and applies through it, and
 * the page's filter definitions are derived from it — one object, registered once.
 *
 * A filter is dormant unless its parameter is sent: {@see self::apply()} only runs for a value
 * that is present. A context filter (members of this space, participants of this conversation)
 * has no definition and authorizes itself in {@see self::parse()} — it checks that the caller
 * may use the context the parameter names and answers an error for it otherwise.
 *
 * The ready filters in `humhub\components\listing\filters` cover the common kinds; a list or a
 * module writes a class only for its own semantics.
 *
 * @since 1.20
 */
abstract class ListFilter
{
    /**
     * The filter's key: its query parameter (and URL parameter).
     */
    abstract public function key(): string;

    /**
     * The request parameters it reads — usually `[key()]`; a date range reads `[from, to]`.
     *
     * @return string[]
     */
    public function params(): array
    {
        return [$this->key()];
    }

    /**
     * Available in this context at all? Features, settings, permissions, purpose. A parameter of
     * an unavailable filter is refused like an unknown one.
     */
    public function isAvailable(ListContext $context): bool
    {
        return true;
    }

    /**
     * Parses the raw parameters into a value, or reports errors (`422 {errors: {param: […]}}`).
     *
     * @param array<string, mixed> $raw the filter's {@see self::params()} the caller sent, by
     *        name — from a request strings and arrays of strings, from PHP callers typed values
     */
    abstract public function parse(array $raw, ListContext $context): FilterValue;

    /**
     * Narrows the list by a present value.
     */
    abstract public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void;

    /**
     * How it is presented, or `null` for a filter without UI (context filters, ids, …).
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        return null;
    }

    /**
     * An invalid value, the message filed under `$param` (default: the key).
     */
    protected function invalid(string $message, ?string $param = null): FilterValue
    {
        return FilterValue::invalid([$param ?? $this->key() => [$message]]);
    }

    /**
     * The message for a value out of the accepted set.
     */
    protected function unknownValue(mixed $value): string
    {
        return Yii::t('base', 'Unknown value "{value}".', ['value' => is_scalar($value) ? (string)$value : '']);
    }
}
