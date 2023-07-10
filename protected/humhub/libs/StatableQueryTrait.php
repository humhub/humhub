<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;

trait StatableQueryTrait
{
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @return array
     */
    public function getReturnedStates(): ?array
    {
        return $this->returnedStates;
    }

    /**
     * @param array|string|null $returnedStates
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     */
    public function setReturnedStates($returnedStates): StatableActiveQueryInterface
    {
        $this->returnedStates = $this->checkStates($returnedStates);

        return $this;
    }

    /**
     * @param array|string|null $state
     *
     * @return StatableQueryInterface|StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setReturnedStates()
     */
    public function andWhereState($state): StatableQueryInterface
    {
        if (null === $state = $this->checkStates($state)) {
            throw new InvalidArgumentException('Empty values cannot be used for this method ' . __METHOD__);
        }

        $this->returnedStates = array_unique(
            $state + $this->returnedStates ?? []
        );

        return $this;
    }

    /**
     * @param array|string|null $states
     *
     * @return int[]|null
     */
    public function checkStates($states): ?array
    {
        /** @var StatableInterface|string $modelClass */
        if (!is_subclass_of($modelClass = $this->modelClass, StatableInterface::class)) {
            throw new InvalidCallException(
                sprintf('The current model class %s does not implement %s', $modelClass, StatableInterface::class)
            );
        }

        if (empty($states)) {
            return null;
        }

        if ($modelClass::validateState($result, $states, [], true, false)) {
            return (array)$result;
        }

        return null;
    }

    /**
     * @param array|string|null $state
     *
     * @return StatableActiveQueryInterface|StatableActiveQuery
     * @see static::setReturnedStates()
     */
    public function whereState($state): StatableActiveQueryInterface
    {
        return $this->setReturnedStates($state);
    }
}
