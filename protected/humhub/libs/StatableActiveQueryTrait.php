<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

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
        $query = parent::prepare($builder);
        $table = $this instanceof ActiveQueryContent ? 'content' : $this->modelClass::tableName();

        if (
            $query->from !== $table
            && ($query->join === null || !in_array($table, $query->join, true))
        ) {
            return $query;
        }

        if (false === strpos($this->stateColumn, '.')) {
            $this->stateColumn = $this->modelClass::tableName() . '.' . $this->stateColumn;
        }

        if (count($this->stateFilterCondition) > 1) {
            $condition = $this->stateFilterCondition;
            $condition[] = [$this->stateColumn => $this->returnedStates];
            $query->andWhere($condition);
        } elseif ($this->returnedStates) {
            $query->andWhere([$this->stateColumn => $this->returnedStates]);
        }

        return $query;
    }
}
