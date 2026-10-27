<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\listing\ListContext;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\TableSettings;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\DropdownMenu;
use yii\helpers\Url;

/**
 * A data table as a `<data-table>` island (`DataTable.vue`, see docs/ui/data-table.md): the
 * definitions of a {@see DataTable} (columns, row actions, filters), the user's column choice,
 * the card's title and header actions, and the endpoint of its rows (a
 * {@see \humhub\components\api\DataTableController}). Until the island mounts, the placeholder
 * shows the card with skeleton rows.
 *
 * A concrete table names its id, table, endpoint and texts:
 *
 * ```php
 * class GroupTable extends DataTableWidget
 * {
 *     public string $tableId = 'admin-groups';
 *
 *     protected function createTable(): DataTable { return Yii::createObject(GroupTableDefinition::class); }
 *     protected function getUrl(): string|array { return ['/admin/api/group/index']; }
 *     …
 * }
 * ```
 *
 * @since 1.20
 */
abstract class DataTableWidget extends VueWidget
{
    /**
     * @var string the table's id: the key of the user's column choice and the remembered rows per
     * page
     */
    public string $tableId = '';

    /**
     * @var string the card's title
     */
    public string $title = '';

    /**
     * @var bool whether the table sits inside another card (e.g. below its tabs) instead of
     * being a card of its own
     */
    public bool $embedded = false;

    /**
     * @var string|array|null the back arrow before the title, on a sub-page leading back to its
     * overview
     */
    public string|array|null $backUrl = null;

    /**
     * @var string|null the back arrow's label (tooltip, `aria-label`), e.g. "Back to overview"
     */
    public ?string $backLabel = null;

    /**
     * @var bool whether the checkboxes of the bulk actions are shown until the user chooses
     * otherwise (lists mostly worked through in bulk, e.g. pending invitations)
     */
    public bool $selectByDefault = false;

    /**
     * @var int the most optional columns shown at once (0: no limit)
     */
    public int $maxColumns = 3;

    /**
     * @inheritdoc
     */
    protected string $component = 'DataTable';

    private ?DataTable $table = null;

    abstract protected function createTable(): DataTable;

    /**
     * @return string|array the endpoint of the rows
     */
    abstract protected function getUrl(): string|array;

    /**
     * @return string the table's accessible name
     */
    abstract protected function getLabel(): string;

    /**
     * @return array `{one, other, range}`: the count texts with `{count}`, `{from}`, `{to}`,
     * `{total}`
     */
    protected function getCountLabels(): array
    {
        return [];
    }

    /**
     * @return CardHeaderActions|null the card's header actions (e.g. "+" to add a record)
     */
    protected function getHeaderActions(): ?CardHeaderActions
    {
        return null;
    }

    /**
     * @return DropdownMenu|null the card's ⋮ menu
     */
    protected function getHeaderMenu(): ?DropdownMenu
    {
        return null;
    }

    public function getTable(): DataTable
    {
        return $this->table ??= $this->createTable();
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return array_merge($this->getTable()->definitions(ListContext::forCurrentUser()), [
            'tableId' => $this->tableId,
            'columnChoice' => TableSettings::columns($this->tableId),
            'columnsUrl' => Url::to(['/user/api/account/table', 'id' => $this->tableId]),
            'maxColumns' => $this->maxColumns,
            'label' => $this->getLabel(),
            'title' => $this->title,
            'url' => Url::to($this->getUrl()),
            'headerActions' => $this->getHeaderActions()?->getEntriesData() ?? [],
            'headerMenu' => $this->getHeaderMenu()?->getEntriesData() ?? [],
            'labels' => $this->getCountLabels(),
            'embedded' => $this->embedded,
            'selectByDefault' => $this->selectByDefault,
            'backUrl' => $this->backUrl === null ? '' : Url::to($this->backUrl),
            'backLabel' => (string)$this->backLabel,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getPlaceholder(): string
    {
        $shown = array_filter(
            $this->getTable()->getColumns(),
            static fn(ColumnDefinition $column) => $column->type !== ColumnDefinition::TYPE_MAIN && $column->visible,
        );

        return $this->render('@humhub/widgets/views/dataTablePlaceholder', [
            'title' => $this->title,
            'embedded' => $this->embedded,
            'backUrl' => $this->backUrl,
            'backLabel' => $this->backLabel,
            'columns' => array_slice(array_values($shown), 0, $this->maxColumns ?: null),
        ]);
    }
}
