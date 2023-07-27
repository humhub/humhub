<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableActiveQueryInterface;
use humhub\interfaces\StatableQueryInterface;
use humhub\modules\content\components\ActiveQueryContent;

trait StatableActiveQueryTrait
{
    use StatableQueryTrait;

    /**
     * State filter that is used for queries. By default, only Published content is returned.
     *
     * Example to include drafts:
     * ```
     * $query = Post::find();
     * $query->stateFilterCondition[] = ['content.state' => StatableInterface::STATE_DRAFT];
     * $posts = $query->readable()->all();
     * ```
     *
     * @since 1.14
     * @var array
     */
    public array $stateFilterCondition = ['OR'];

    protected ?array $returnedStates = null;

    public function init()
    {
        $this->initReturnedStates();

        parent::init();
    }

    public function initReturnedStates(): self
    {
        $this->returnedStates = $this->getModelClass()::getStateServiceTemplate()->getDefaultQueriedStates();

        $this->trigger(StatableQueryInterface::EVENT_INIT_DEFAULT_QUERIED_STATES);

        return $this;
    }

    public function setMultiple(bool $multiple = true): StatableActiveQueryInterface
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($builder)
    {
        $modelClass = $this->getModelClass();
        $query = parent::prepare($builder);
        $table = $this instanceof ActiveQueryContent ? 'content' : $modelClass::tableName();

        if (
            (is_array($query->from) ? !in_array($table, $query->from, true) : $query->from !== $table)
            && ($query->join === null || !in_array($table, $query->join, true))
        ) {
            return $query;
        }

        $stateColumn = $modelClass::getStateServiceTemplate()->getField();

        if (false === strpos($stateColumn, '.')) {
            $stateColumn = $table . '.' . $stateColumn;
        }

        if (count($this->stateFilterCondition) > 1) {
            $condition = $this->stateFilterCondition;
            $condition[] = [$stateColumn => $this->returnedStates];
            $query->andWhere($condition);
        } elseif ($this->returnedStates) {
            $query->andWhere([$stateColumn => $this->returnedStates]);
        }

        return $query;
    }
}
