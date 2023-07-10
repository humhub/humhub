<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\ActiveRecord;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidStateException;
use Throwable;
use Traversable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
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
    public function setState(
        $state,
        array $options = []
    ) {
        if (!$this->canChangeState($state, $options)) {
            return;
        }

        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function canChangeState(
        $state,
        array $options = []
    ): bool {
        if ($state === null || $state === '') {
            return false;
        }

        return !empty(static::getAllowedStates($state, $options));
    }

    /**
     * @inheritdoc
     */
    public function validateStateAttribute(
        string $attribute,
        $params,
        InlineValidator $validator,
        $current
    ): bool {
        $attribute ??= 'state';
        $current ??= $this->$attribute;
        $state = $current;

        if (!static::validateState($result, $state, $params)) {
            $this->addError($attribute, $result);

            return false;
        }

        if ($result !== $current) {
            $this->$attribute = current(static::STATES_AVAILABLE);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function getAllowedStates(
        ?string $filterByState = null,
        ?array $options = [],
        bool $stopOnFirstMatch = false
    ): array {
        if ($filterByState === null || trim($filterByState) === '') {
            return static::STATES_AVAILABLE;
        }

        $found = false;

        return array_filter(
            static::STATES_AVAILABLE,
            static function (
                int $allowedStateId,
                $allowedStateKey
            ) use (
                &
                $found,
                $filterByState,
                $stopOnFirstMatch
            ): bool {
                return ($found === false || $stopOnFirstMatch === false)
                    && $found = (
                        $allowedStateId === filter_var($filterByState, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
                        || (!is_int($allowedStateKey) && $allowedStateKey === $filterByState)
                    );
            }
        );
    }

    public static function getQueryDefaultStates(): ?array
    {
        $query = static::find();

        if (!$query instanceof StatableActiveQueryInterface) {
            return null;
        }

        return $query->returnedStates;
    }

    /**
     * @inheritdoc
     */
    public static function getStateByName($state = null): array
    {
        $found = false;

        return array_filter(
            self::getAllowedStates(),
            static fn(
                int $currentState,
                $key
            ): bool => $state === null
                || ($found === false
                    && $found = (
                        $currentState === filter_var($state, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
                        || (!is_int($key) && $key === $state)
                    )
                )
        );
    }

    /**
     * @inheritdoc
     */
    public static function getStateNames($state = null): array
    {
        $states = static::getStateByName($state);

        array_walk(
            $states,
            static function (
                &$currentState,
                $key,
                $translationBase
            ) {
                $currentState = Yii::t($translationBase, $key);
            },
            static::$translationBase ?? 'base'
        );

        return $states;
    }

    public static function isStateInList(
        string $state,
        ?array &$states = null
    ): ?int {
        $states ??= static::getAllowedStates();

        switch (true) {
            case is_int($saveState = $state):
            case null !== $saveState = filter_var($state, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE):
            case null !== $saveState = $states[$state] ?? null:
                break;
            default:
                $saveState = null;
        }

        if ($saveState === null || !in_array($saveState, $states, true)) {
            return null;
        }

        return (int)$saveState;
    }

    /**
     * @inheritdoc
     */
    public static function validateState(
        &$result,
        $state,
        ?array $params = [],
        bool $allowArray = false,
        bool $throwException = true
    ): bool {
        static $returnError;

        // if states are empty, use the default state instead
        if ($state === null || $state === '') {
            $result = current(static::STATES_AVAILABLE);

            return true;
        }

        /**
         * @param Throwable $error
         *
         * @return false
         * @throws Throwable
         */
        $returnError ??= static function (Throwable $error) use (&$result, $throwException) {
            if ($throwException) {
                throw $error;
            }

            $result = $error;

            return false;
        };

        if (is_iterable($state) && !$allowArray) {
            return $returnError(
                new InvalidArgumentTypeException(
                    __METHOD__,
                    [1 => '$state'],
                    ['iterable', 'array', Traversable::class],
                    $state,
                    false,
                    'if parameter #3 ($allowArray) is true'
                )
            );
        }

        if ($state === self::STATES_AVAILABLE) {
            $result = $state;

            return true;
        }

        $availableStates = null;
        $allowedStates = static::getAllowedStates(null, $params ??= []);
        $invalidStates = null;
        $isSingleState = !is_iterable($state);
        $states = ArrayHelper::toArray($state);

        foreach ($states as &$state) {
            if (null !== $saveState = static::isStateInList($state, $allowedStates)) {
                $state = $saveState;

                continue;
            }

            $availableStates ??= static::getAllowedStates();

            if (null !== $stateExists = static::isStateInList($state, $availableStates)) {
                $name = strtr("{state_name} ({state_id}:{state_key}", [
                    '{state_name}',
                    '{state_id}' => $stateExists,
                    '{state_key}' => array_search($stateExists, $availableStates, true),
                ]);

                $error = Yii::t(
                    static::$translationBase ?? 'base',
                    'The selected state "{state}" is not valid.',
                    [
                        'state' => $name,
                    ]
                );
            } else {
                $error = Yii::t(
                    static::$translationBase ?? 'base',
                    'The selected state "{state}" is unknown.',
                    [
                        'state' => $state,
                    ]
                );
            }

            $invalidStates[$error] = $state;
            $state = null;
        }
        unset($state);

        if ($invalidStates === null) {
            $result = $isSingleState
                ? reset($states)
                : $states;

            return true;
        }

        return $returnError(
            new InvalidStateException(
                sprintf(
                    'The following states are not allowed for the current model %s: %s',
                    static::class,
                    implode(', ', $invalidStates)
                ),
                0,
                null,
                $invalidStates,
                $allowedStates,
                $params
            )
        );
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
}
