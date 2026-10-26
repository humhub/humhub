<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\table;

use humhub\components\listing\FilterableList;
use humhub\components\listing\ListContext;
use yii\base\Component;

/**
 * The server side of a table of records shown by the `DataTable` Vue component
 * (docs/ui/data-table.md): its list ({@see self::getList()}, the filters and sorts of a
 * {@see FilterableList}), its columns ({@see ColumnDefinition}) and row actions ({@see RowAction}),
 * and the row of a record ({@see self::row()}).
 *
 * The page hands the definitions to the component ({@see self::definitions()}); the API endpoint
 * the component loads builds the list from the request and answers the rows:
 *
 * ```php
 * $table = new UserTable();
 * $query = $table->getList()->build($this->listParams(), ListContext::forCurrentUser())->query();
 * $pagination = $this->handlePagination($query);
 * return $this->returnPagination($pagination, array_map($table->row(...), $query->all()));
 * ```
 *
 * Modules add, replace or remove columns and actions on {@see self::EVENT_INIT}, and narrow the
 * rows on the list's own events (`FilterableList::EVENT_BUILD`):
 *
 * ```php
 * ['class' => UserTable::class, 'event' => UserTable::EVENT_INIT, 'callback' => [Events::class, 'onUserTableInit']],
 *
 * public static function onUserTableInit(Event $event)
 * {
 *     $event->sender->addAction(new RowAction('reset-twofa', Yii::t(…, 'Reset two-factor authentication'),
 *         RowAction::KIND_POST, fn(User $user) => ['/twofa/admin/reset-user', 'id' => $user->id], …));
 * }
 * ```
 *
 * @since 1.20
 */
abstract class DataTable extends Component
{
    public const EVENT_INIT = 'init';

    /**
     * @var ColumnDefinition[] by key
     */
    private array $columns = [];

    /**
     * @var RowAction[] by id
     */
    private array $actions = [];

    private ?FilterableList $list = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->columns() as $column) {
            $this->addColumn($column);
        }
        foreach ($this->actions() as $action) {
            $this->addAction($action);
        }

        $this->trigger(self::EVENT_INIT);
    }

    /**
     * @return ColumnDefinition[] the table's own columns
     */
    abstract protected function columns(): array;

    /**
     * @return RowAction[] the table's own row actions
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * The list whose records are the rows.
     */
    abstract protected function createList(): FilterableList;

    /**
     * The cells of a record by column key (see {@see ColumnDefinition} for their shape); a cell
     * of a column the table does not have is dropped.
     */
    abstract protected function cells($model): array;

    /**
     * Whether the viewer may see the table, its page and its endpoint. A table restricted to some
     * viewers overrides it (the endpoint has no access rules of its own to extend).
     */
    public function canAccess(): bool
    {
        return true;
    }

    /**
     * The key of a record's row.
     */
    protected function rowId($model): int|string
    {
        return $model->id;
    }

    public function getList(): FilterableList
    {
        return $this->list ??= $this->createList();
    }

    /**
     * Adds a column, or replaces the one with the same key.
     */
    public function addColumn(ColumnDefinition $column): void
    {
        $this->columns[$column->key] = $column;
    }

    public function removeColumn(string $key): void
    {
        unset($this->columns[$key]);
    }

    public function getColumn(string $key): ?ColumnDefinition
    {
        return $this->columns[$key] ?? null;
    }

    /**
     * @return ColumnDefinition[] in their `sortOrder` (equal ones in the order they were added)
     */
    public function getColumns(): array
    {
        $columns = array_values($this->columns);
        usort($columns, static fn(ColumnDefinition $a, ColumnDefinition $b) => $a->sortOrder <=> $b->sortOrder);

        return $columns;
    }

    /**
     * Adds a row action, or replaces the one with the same id.
     */
    public function addAction(RowAction $action): void
    {
        $this->actions[$action->id] = $action;
    }

    public function removeAction(string $id): void
    {
        unset($this->actions[$id]);
    }

    public function getAction(string $id): ?RowAction
    {
        return $this->actions[$id] ?? null;
    }

    /**
     * @return RowAction[] in their `sortOrder`, the destructive ones last
     */
    public function getActions(): array
    {
        $actions = array_values($this->actions);
        usort($actions, static fn(RowAction $a, RowAction $b) => [$a->destructive, $a->sortOrder] <=> [$b->destructive, $b->sortOrder]);

        return $actions;
    }

    /**
     * The row of a record: `{id, cells: {<column>: cell}, actions: [{id, url}]}` — the actions
     * this record offers, in their order.
     */
    public function row($model): array
    {
        $cells = $this->cells($model);
        $row = [];
        foreach ($this->columns as $key => $column) {
            $cell = $column->value !== null ? ($column->value)($model) : ($cells[$key] ?? null);
            if ($cell !== null) {
                $row[$key] = $cell;
            }
        }

        $actions = [];
        foreach ($this->getActions() as $action) {
            if ($action->isVisibleFor($model)) {
                $actions[] = ['id' => $action->id, 'url' => $action->urlFor($model)];
            }
        }

        return [
            'id' => $this->rowId($model),
            'cells' => (object)$row,
            'actions' => $actions,
        ];
    }

    /**
     * The definitions the `DataTable` component takes: `columns`, `actions` and the list's
     * `filters` (see {@see FilterableList::definitions()}).
     */
    public function definitions(ListContext $context): array
    {
        return [
            'columns' => array_map(static fn(ColumnDefinition $column) => $column->toArray(), $this->getColumns()),
            'actions' => array_map(static fn(RowAction $action) => $action->toArray(), $this->getActions()),
            'filters' => $this->getList()->definitions($context),
        ];
    }
}
