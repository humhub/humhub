<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing\filters;

use humhub\components\listing\ListContext;

/**
 * A filter whose control suggests records before anything is typed (the core's `SpaceFilter`,
 * `UserFilter` and `TopicFilter`, through {@see SuggestingFilter}).
 *
 * The suggestions are loaded lazily: a list that answers them
 * ({@see \humhub\components\listing\FilterableList::suggestionsUrl()}) puts the URL into the
 * filter's definition (`props.suggestionsUrl`), and the control requests it when its empty field
 * first gets the focus — the endpoint answering
 * {@see \humhub\components\listing\FilterableList::suggestions()} as `{results: [...]}`.
 *
 * @since 1.20
 */
interface HasSuggestions
{
    /**
     * The most suggestions a filter lists.
     */
    public const LIMIT = 6;

    /**
     * Whether the filter has suggestions at all (a list gave it a source).
     */
    public function hasSuggestions(): bool;

    /**
     * The suggestions for the context's user: at most {@see self::LIMIT} items, the most relevant
     * first, in the shape of the filter's picker search.
     */
    public function suggestions(ListContext $context): array;
}
