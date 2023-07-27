<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\ActiveRecord;
use humhub\interfaces\StatableActiveQueryInterface;
use humhub\interfaces\StateServiceInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\validators\InlineValidator;

/**
 * @property int $state
 * @since 1.15
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


    /**
     * @inheritdoc
     * @param mixed $condition primary key value or a set of column values
     * @param array|null $allowedStates
     * @return static|null
     * @throws InvalidConfigException
     */
    public static function findOne($condition, $allowedStates = null): ?ActiveRecord
    {
        return static::findByCondition($condition, $allowedStates)->one();
    }

    /**
     * @inheritdoc
     * @param mixed $condition primary key value or a set of column values
     * @param $allowedStates
     * @return static[]
     * @throws InvalidConfigException
     */
    public static function findAll($condition, $allowedStates = null): array
    {
        return static::findByCondition($condition, $allowedStates)->all();
    }

    /**
     * @inheritdoc
     * @param mixed $condition primary key value or a set of column values
     * @param $allowedStates
     * @return StatableActiveQueryInterface|ActiveQuery
     * @throws InvalidConfigException
     */
    protected static function findByCondition($condition, $allowedStates = null): ActiveQuery
    {
        if (empty($condition)) {
            $query = static::find();
            return $query->setReturnedStates($allowedStates);
        }

        /**
         * @noinspection PhpPossiblePolymorphicInvocationInspection
         * @noinspection PhpInternalEntityUsedInspection
         */
        return parent::findByCondition($condition)->setReturnedStates($allowedStates);
    }

    public static function getQueryDefaultStates(): ?array
    {
        $query = static::find();

        if (!$query instanceof StatableActiveQueryInterface) {
            return null;
        }

        return $query->getReturnedStates();
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
