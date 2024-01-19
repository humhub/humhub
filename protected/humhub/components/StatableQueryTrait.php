<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\StatableActiveQueryInterface;
use humhub\interfaces\StatableInterface;
use humhub\interfaces\StatableQueryInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;

/**
 * @property int[]|null $stateFilterList
 */
trait StatableQueryTrait
{
    protected ?array $stateFilterList = null;

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
     * @since 1.16
     * @see StatableQueryTrait::$stateFilterList
     * @var array
     */
    protected array $stateFilterCondition;

    /**
     * @return StatableInterface|string
     */
    public function getModelClass(): string
    {
        if (!is_subclass_of($modelClass = $this->modelClass, StatableInterface::class)) {
            throw new InvalidCallException(
                sprintf('The current model class %s does not implement %s', $modelClass, StatableInterface::class)
            );
        }

        return $modelClass;
    }

    /**
     * @return array
     */
    public function getStateFilterList(): ?array
    {
        return $this->stateFilterList;
    }

    /**
     * @param array|string|null $stateFilterList
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     */
    public function setStateFilterList($stateFilterList): StatableActiveQueryInterface
    {
        $this->stateFilterList = $this->checkStates($stateFilterList);

        return $this;
    }

    public function setStateFilterCondition(?array $stateFilterCondition): StatableActiveQueryInterface
    {
        $this->stateFilterCondition = $stateFilterCondition ?? ['OR'];
        return $this;
    }

    public function getStateFilterCondition(): array
    {
        return $this->stateFilterCondition;
    }

    /**
     * @param array|string|null $states
     *
     * @return int[]|null
     */
    public function checkStates($states): ?array
    {
        if (empty($states) && $states !== 0 && $states !== '0') {
            return null;
        }

        if ($this->getModelClass()::validateState($result, $states, [], true, false)) {
            return (array)$result;
        }

        return null;
    }

    /**
     * @param array|string|null $state
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function whereState($state): StatableActiveQueryInterface
    {
        return $this->setStateFilterList($state);
    }

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function andWhereState($state): StatableQueryInterface
    {
        if (null === $state = $this->checkStates($state)) {
            throw new InvalidArgumentException('Empty values cannot be used for this method ' . __METHOD__);
        }

        $this->setStateFilterList(array_unique(
            $state + ($this->getStateFilterList() ?? [])
        ));

        return $this;
    }

    /**
     * Resets the returned state
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function whereStateAny(): StatableActiveQueryInterface
    {
        return $this->setStateFilterList(null);
    }
}
