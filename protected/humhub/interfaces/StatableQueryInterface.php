<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\components\StatableActiveQuery;

interface StatableQueryInterface extends FilterableQueryInterface
{
    /**
     * @event Event an event that is triggered when only visible users are requested via [[visible()]].
     */
    public const EVENT_INIT_DEFAULT_QUERIED_STATES = 'setInitDefaultQueriedStates';

    /**
     * @return StatableInterface
     * @noinspection PhpDocSignatureInspection
     */
    public function getModelClass(): string;

    public function getStateFilterList(): ?array;

    /**
     * @param array|string|null $stateFilterList
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     */
    public function setStateFilterList($stateFilterList): StatableQueryInterface;

    public function setStateFilterCondition(?array $stateFilterCondition): self;

    public function getStateFilterCondition(): array;

    /**
     * @param array|string|null $states
     *
     * @return int[]|null
     */
    public function checkStates($states): ?array;

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function whereState($state): StatableQueryInterface;

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function andWhereState($state): StatableQueryInterface;

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setStateFilterList()
     */
    public function whereStateAny(): StatableQueryInterface;
}
