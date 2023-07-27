<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\libs\StatableActiveQuery;

interface StatableQueryInterface
{
    /**
     * @event Event an event that is triggered when only visible users are requested via [[visible()]].
     */
    public const EVENT_INIT_DEFAULT_QUERIED_STATES = 'setInitDefaultQueriedStates';

    /**
     * @return string|StatableInterface
     */
    public function getModelClass(): string;

    public function getReturnedStates(): ?array;

    /**
     * @param array|string|null $returnedStates
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     */
    public function setReturnedStates($returnedStates): StatableQueryInterface;

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
     * @see static::setReturnedStates()
     */
    public function andWhereState($state): StatableQueryInterface;

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setReturnedStates()
     */
    public function whereState($state): StatableQueryInterface;
}
