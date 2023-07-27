<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\EventWithTypedResult;
use humhub\exceptions\InvalidArgumentTypeException;
use yii\helpers\ArrayHelper;

trait EmitFilterTrait
{
    /**
     * @param string $name Event name
     * @param mixed $value
     * @param array|string|null $allowedTypes
     * @param bool|null $nullable
     * @param bool|null $readonly
     * @param EventWithTypedResult|string|null $event
     *
     * @return mixed
     */
    protected function filterValue(
        string $name,
        $value,
        ?array $allowedTypes = null,
        ?bool $nullable = true,
        ?bool $readonly = false,
        $event = EventWithTypedResult::class
    ) {

        return $this->filterValueCreate($value, $allowedTypes, $nullable, $readonly, $event)
                    ->fire($name, $this)
                    ->getValue();
    }


    /**
     * @param mixed $value
     * @param array|string|null $allowedTypes
     * @param bool|null $nullable
     * @param bool|null $readonly
     * @param EventWithTypedResult|string|array|null $event
     *
     * @return mixed
     */
    protected function filterValueCreate(
        $value,
        ?array $allowedTypes = null,
        ?bool $nullable = true,
        ?bool $readonly = false,
        $event = EventWithTypedResult::class
    ): EventWithTypedResult {

        $config = [];

        if ($value instanceof EventWithTypedResult) {
            $event = $value;
            $value = null;
        } else {
            while (! $event instanceof EventWithTypedResult) {
                if ($event === null) {
                    $event = new EventWithTypedResult();
                    break;
                }

                if (is_array($event)) {
                    $config = $event;
                    $event  = ArrayHelper::remove($config, 'class', EventWithTypedResult::class);
                }

                if (! is_a($event, EventWithTypedResult::class, true)) {
                    throw new InvalidArgumentTypeException(__METHOD__, [ 6 => $event ], EventWithTypedResult::class, $event);
                }

                $event = new $event($config);
            }
        }

        if ($nullable === false) {
            $event->setNullable(false);
        }

        if ($allowedTypes !== null) {
            $event->setAllowedTypes($allowedTypes);
        }

        return $event->setValue($value)
                     ->setImmutable($readonly);
    }
}
