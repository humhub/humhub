<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\events\ActiveQueryEvent;
use humhub\interfaces\FilterableQueryInterface;
use humhub\interfaces\StatableActiveQueryInterface;
use humhub\interfaces\StatableQueryInterface;
use humhub\modules\content\components\ActiveQueryContent;
use humhub\modules\stream\models\StreamQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\TableSchema;

trait StatableActiveQueryTrait
{
    use StatableQueryTrait;


    public function init()
    {
        $this->initReturnedStates();

        parent::init();
    }

    public function initReturnedStates(): self
    {
        // reset the states
        $this->whereDefaultFilter(['context' => FilterableQueryInterface::FILTER_CONTEXT_INIT]);

        // allow for the states to be checked/altered through event listeners
        $this->trigger(StatableQueryInterface::EVENT_INIT_DEFAULT_QUERIED_STATES);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function whereDefaultFilter(?array $config = null): self
    {
        // reset the states
        $this->whereStateAny();

        // reset any other condition known to us
        if ($this instanceof StreamQuery) {
            $this->query()->where = null;
        }

        return $this->andWhereDefaultFilter($config);
    }

    /**
     * @inheritdoc
     */
    public function andWhereDefaultFilter(?array $config = null): self
    {
        // get the default states from the StateService
        $DefaultReturnedStates = $this->getModelClass()::getStateServiceTemplate()->getDefaultQueriedStates($config);

        if (StateService::isExtendedStateFilter($DefaultReturnedStates)) {
            $this->setStateFilterCondition($DefaultReturnedStates);
        } else {
            $this->setStateFilterList($this->checkStates($DefaultReturnedStates));
        }

        $this->trigger(FilterableQueryInterface::EVENT_WHERE_DEFAULT_FILTER, new ActiveQueryEvent(['query' => $this]));

        return $this;
    }

    /**
     * Resets the returned state
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function whereStateAny(): StatableQueryInterface
    {
        return $this->setStateFilterCondition(null)->setStateFilterList(null);
    }

    public function setMultiple(bool $multiple = true): StatableActiveQueryInterface
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function prepare($builder)
    {
        $query = parent::prepare($builder);

        $stateFilterList = $this->getStateFilterList();
        $stateFilterCondition = $this->getStateFilterCondition();

        if (empty($stateFilterList) && count($stateFilterCondition) === 1) {
            return $query;
        }

        $modelClass = $this->getModelClass();
        $stateService = $modelClass::getStateServiceTemplate();
        $stateColumn = $stateService->getField();
        $tableName = $modelClass::tableName();
        $table = $this instanceof ActiveQueryContent ? 'content' : $tableName;
        $table = static::prependOrReturnTableNameFromColumn($stateColumn, $table) ?? $table;
        $alias = $this->aliasFromSourceTables($table);
        $tableName = $this->aliasFromSourceTables($tableName);

        if ($alias === null) {
            if (!($this instanceof ActiveQueryContent && $table === 'content')) {
                throw new InvalidConfigException("Table of state field ($$stateColumn) is not part of the query.");
            }

            $stateColumn = $stateService->getField();
            $alias = 'content_state_2311';
            static::prependOrReturnTableNameFromColumn($stateColumn, $alias);

            /** @var TableSchema $tableSchema */
            $tableSchema = $modelClass::getTableSchema();
            $primaryKey = $tableSchema->primaryKey;
            $query->join(
                'INNER JOIN',
                "content AS $alias",
                sprintf(
                    "$alias.object_model=:content_object_model AND $alias.object_id=$tableName.%s",
                    reset($primaryKey)
                ),
                [':content_object_model' => $modelClass]
            );

            if ($query->select === null) {
                $query->select = [];
                foreach ($this->aliasesFromSourceTables() as $sourceTable) {
                    $query->select[] = "$sourceTable.*";
                }
            } else {
                xdebug_break();
            }

            // If joins have been given, then let's assume that all the criteria have qualified names already.
            // Otherwise, attempt to add the table name to unqualified names
            if (empty($this->join)) {
                $this->prefixTableNameToUnqualifiedColumnNames($tableName, $query, $tableSchema);
            }
        } elseif ($alias !== $table) {
            $stateColumn = $stateService->getField();
            static::prependOrReturnTableNameFromColumn($stateColumn, $alias);
        }

        if (count($stateFilterCondition) > 1) {
            $condition = $stateFilterCondition;
            $condition[] = [$stateColumn => $stateFilterList];
            $query->andWhere($condition);
        } elseif ($stateFilterList) {
            $query->andWhere([$stateColumn => $stateFilterList]);
        }

        return $query;
    }

    protected static function defaultTableNameForWhereClauses(&$where, string $tableName, array &$fields): int
    {
        static $pattern = null;

        $count = 0;

        if (empty($fields)) {
            return $count;
        }

        if (is_array($where)) {
            foreach ($where as $key => &$value) {
                if ($key === 0 && is_string($value) && preg_match('@^(?:AND|OR|IS)$@i', $value)) {
                    continue;
                }

                if (is_string($key)) {
                    if (!in_array($key, $fields, true)) {
                        continue;
                    }

                    $oldColumn = $key;
                    if (null === static::prependOrReturnTableNameFromColumn($key, $tableName)) {
                        $where[$key] = &$where[$oldColumn];
                        unset($where[$oldColumn]);
                        $count++;
                    }
                    continue;
                }

                if ($value instanceof ExpressionInterface) {
                    continue;
                }

                $count += static::defaultTableNameForWhereClauses($value, $tableName, $fields);
            }

            return $count;
        }

        if ($pattern === null) {
            $schema = Yii::$app->getDb()->getSchema();

            $quoted = $schema->quoteColumnName("a b");
            $startingCharacter = preg_quote(preg_replace("/a b.*/", '', $quoted), "@");
            $endingCharacter = preg_quote(preg_replace("/.*a b/", '', $quoted), "@");

            $pattern = "@(?!\.)\b(?:$startingCharacter)?%s(?:$endingCharacter)@";
        }

        preg_replace(sprintf($pattern, implode("|", $fields)), $tableName . "\$1", $where, null, $x);

        return $count + $x;
    }

    protected function &aliasesFromSourceTables(): array
    {
        $aliases = [];

        if (empty($this->from)) {
            $aliases[] = ($this->primaryModel ?? $this->modelClass)::tableName();
        } else {
            foreach ((array)$this->from as $alias => $tableName) {
                $aliases[] = is_string($alias) ? $alias : static::aliasExtractFromString($tableName) ?? $tableName;
            }
        }

        if ($this->join === null) {
            return $aliases;
        }

        foreach ($this->join as $join) {
            $aliases[] = static::aliasExtractFromString($join[1]) ?? $join[1];
        }

        return $aliases;
    }

    public static function aliasExtractFromString(string $string, ?string $table = null): ?string
    {
        if (preg_match('/^(.*?)(?:\s+AS\s+|\s+)({{\w+}}|\w+)$/i', $string, $matches) && ($table === null || $matches[1] === $table)) {
            return $matches[2];
        }
        return null;
    }

    protected function aliasFromSourceTables(string $table, ?string $joinType = 'INNER JOIN', ?array &$join = []): ?string
    {
        $join = null;

        if (!empty($this->from)) {
            foreach ((array)$this->from as $alias => $tableName) {
                if ($table === $tableName) {
                    return is_string($alias) ? $alias : $tableName;
                }

                if ($alias = static::aliasExtractFromString($tableName, $table)) {
                    return $alias;
                }
            }
        }

        if ($this->join === null) {
            return null;
        }

        foreach ($this->join as $join) {
            if ($joinType !== null && $join[0] !== $joinType) {
                continue;
            }

            if ($join[1] === $table) {
                return $table;
            }

            if ($alias = static::aliasExtractFromString($join[1], $table)) {
                return $alias;
            }
        }

        $join = null;

        return null;
    }

    /**
     * @param string $column Column name to be checked, and prepended with $table if no table is already given.
     * @param string $table Default table name to be used.
     *
     * @return string|null null if $column has been prepended with $table, or the table name found in $column.
     */
    protected static function prependOrReturnTableNameFromColumn(string &$column, string $table): ?string
    {
        if (false === $pos = strpos($column, '.')) {
            $column = $table . '.' . $column;
            return null;
        }

        return substr($column, 0, $pos);
    }

    public function prefixTableNameToUnqualifiedColumnNames(?string $tableName = null, ?Query $query = null, ?TableSchema $tableSchema = null)
    {
        $tableName ??= $this->aliasFromSourceTables($tableName = $this->getModelClass()::tableName()) ?? $tableName;
        $query ??= $this;
        $tableSchema ??= $this->getModelClass()::getTableSchema();
        $columns = $tableSchema->getColumnNames();

        if ($query instanceof StreamQuery) {
            $query = $query->query(true);
        }

        static::defaultTableNameForWhereClauses($query->where, $tableName, $columns);

        foreach (['orderBy', 'groupBy'] as $array) {
            if (is_array($this->$array)) {
                foreach (array_intersect(array_keys($this->$array), $columns) as $column) {
                    $oldColumn = $column;
                    if (null === static::prependOrReturnTableNameFromColumn($column, $tableName)) {
                        $query->$array[$column] = &$query->$array[$oldColumn];
                        unset($query->$array[$oldColumn]);
                    }
                }
            }
        }
    }
}
