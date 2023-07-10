<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

interface StatableQueryInterface
{
    public function getModelClass(): string;

    public function getReturnedStates(): ?array;


    /**
     * @param array|string|null $returnedStates
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     */
    public function setReturnedStates($returnedStates): StatableQueryInterface;

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setReturnedStates()
     */
    public function andWhereState($state): StatableQueryInterface;

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
    public function whereState($state): StatableQueryInterface;
}
