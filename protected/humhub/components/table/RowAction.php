<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\table;

use Closure;
use yii\helpers\Url;

/**
 * An action on a row of a {@see DataTable}: defined once per table, offered on each row it is
 * {@see self::isVisibleFor()} (with the row's own URL). See docs/ui/data-table.md, "Row actions".
 *
 * Kinds:
 * - `link`: opens the URL
 * - `modal`: opens the URL in the global modal
 * - `post`: submits a POST to the URL (a web route answering with a redirect, e.g. impersonate)
 * - `request`: POSTs to the URL (an API endpoint) and updates the row in place with the answer's
 *   `row`, or removes it when the answer has `removed: true`; any other answer reloads the page
 *
 * An action with a `bulkUrl` is also offered for the selected rows: the table POSTs `ids` (or
 * `all` with its filters in the query) to that URL, which applies it to every one of them whose
 * row offers it and answers `{done, skipped, message}`.
 *
 * @since 1.20
 */
class RowAction
{
    public const KIND_LINK = 'link';
    public const KIND_MODAL = 'modal';
    public const KIND_POST = 'post';
    public const KIND_REQUEST = 'request';

    public readonly Closure $url;
    public readonly ?Closure $visible;

    /**
     * @param callable $url `fn($model): string|array` the action's URL for a row (a route array
     *        is passed through {@see Url::to()})
     * @param string|null $icon a Tabler icon name, shown when the row has this action only
     * @param string|null $confirm a question asked before the action runs
     * @param bool $destructive shown last and in red; asks for confirmation when `confirm` is set
     * @param callable|null $visible `fn($model): bool` whether a row offers the action
     * @param string|array|null $bulkUrl the endpoint applying the action to the selected rows
     * @param bool $bulkModal whether `bulkUrl` is a dialog (loaded with `ids[]`, or `all` and the
     *        list's filters, in the query) instead of an endpoint the table POSTs to, e.g. to
     *        write a message for the selected rows
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $kind,
        callable $url,
        public readonly ?string $icon = null,
        public readonly ?string $confirm = null,
        public readonly bool $destructive = false,
        ?callable $visible = null,
        public readonly int $sortOrder = 1000,
        public readonly string|array|null $bulkUrl = null,
        public readonly bool $bulkModal = false,
    ) {
        $this->url = Closure::fromCallable($url);
        $this->visible = $visible !== null ? Closure::fromCallable($visible) : null;
    }

    public function isVisibleFor($model): bool
    {
        return $this->visible === null || (bool)($this->visible)($model);
    }

    public function urlFor($model): string
    {
        return Url::to(($this->url)($model));
    }

    /**
     * The definition the `DataTable` component takes (its `actions` prop).
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'label' => $this->label,
            'kind' => $this->kind,
            'icon' => $this->icon,
            'confirm' => $this->confirm,
            'destructive' => $this->destructive ?: null,
            'bulkUrl' => $this->bulkUrl !== null ? Url::to($this->bulkUrl) : null,
            'bulkModal' => $this->bulkModal ?: null,
        ], static fn($item) => $item !== null);
    }
}
