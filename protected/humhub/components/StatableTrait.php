<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\StateServiceInterface;
use yii\validators\InlineValidator;

/**
 * @property int $state
 * @since 1.16
 */
trait StatableTrait
{
    /**
     * @inheritdoc
     */
    public function canChangeState($state, array $options = []): bool
    {
        return $state !== null && $state !== '' && $this->getStateService()->canChange($state, $options);
    }

    public function getStateService(): StateServiceInterface
    {
        return static::getStateServiceClass()::factory($this);
    }

    /**
     * @return string|StateServiceInterface
     */
    abstract public static function getStateServiceClass(): string;

    /**
     * @inheritdoc
     */
    public function validateStateAttribute(string $attribute, $params, InlineValidator $validator, $current): bool
    {
        $attribute ??= static::getStateServiceTemplate()->getField();
        $current ??= $this->$attribute;
        $state = $current;

        if (!static::validateState($result, $state, $params)) {
            $this->addError($attribute, $result);

            return false;
        }

        if ($result !== $current) {
            $this->$attribute = current(static::getStateServiceTemplate()->getStates());
        }

        return true;
    }

    public static function find($allowedStates = null): StatableActiveQuery
    {
        return new StatableActiveQuery(static::class);
    }

    public static function getQueryDefaultStates(?array $config = null): ?array
    {
        return static::getStateServiceTemplate()->getDefaultQueriedStates($config);
    }

    public static function getStateServiceTemplate(): StateServiceInterface
    {
        return static::getStateServiceClass()::factory(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function validateState(&$result, $state, ?array $params = [], bool $allowArray = false, bool $throwException = true): bool
    {
        return static::getStateServiceTemplate()->validateState($result, $state, $params, $allowArray, $throwException);
    }
}
