<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing\filters;

use Closure;
use humhub\components\listing\ListContext;

/**
 * The {@see HasSuggestions} part of a picker filter (a space, a person, a topic): what its
 * control lists as soon as the empty field gets the focus, before anything is typed.
 *
 * The list knows what is worth suggesting — the authors of its latest items, say — and passes
 * `suggest`: `fn(ListContext $context, int $limit): int[]`, the ids of at most `$limit` records,
 * the most relevant first. {@see self::suggestions()} keeps those the filter's picker would list
 * to the viewer, in that order, in the shape the picker's search answers.
 *
 * Computed only when asked: the list's endpoint answers them when the control first opens (see
 * {@see \humhub\components\listing\FilterableList::suggestions()}), not with every page.
 *
 * @since 1.20
 */
trait SuggestingFilter
{
    protected ?Closure $suggest = null;

    protected function setSuggest(?callable $suggest): void
    {
        $this->suggest = $suggest !== null ? Closure::fromCallable($suggest) : null;
    }

    /**
     * @inheritdoc
     */
    public function hasSuggestions(): bool
    {
        return $this->suggest !== null;
    }

    /**
     * @inheritdoc
     */
    public function suggestions(ListContext $context): array
    {
        if ($this->suggest === null) {
            return [];
        }

        $ids = [];
        foreach (($this->suggest)($context, HasSuggestions::LIMIT) as $id) {
            $id = (int)$id;
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        $ids = array_slice($ids, 0, HasSuggestions::LIMIT);

        if ($ids === []) {
            return [];
        }

        $items = $this->suggestionItems($ids, $context);

        return array_values(array_filter(array_map(static fn(int $id) => $items[$id] ?? null, $ids)));
    }

    /**
     * The items of those ids the viewer may pick, by id, in the shape of the picker's search.
     *
     * @param int[] $ids
     * @return array<int, array>
     */
    abstract protected function suggestionItems(array $ids, ListContext $context): array;
}
