<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\components\StatableActiveQueryTrait;
use humhub\components\StatableQueryTrait;
use humhub\events\EventWithTypedValue;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidStateException;
use humhub\interfaces\FilterableQueryInterface;
use humhub\interfaces\StatableInterface;
use humhub\interfaces\StateServiceInterface;
use humhub\libs\DbDateValidator;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use Throwable;
use Traversable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This service is used to extend Content record for state features
 *
 * @since 1.16
 *
 * @property-read ActiveRecord $stateRecord
 * @property-read array $stateContexts
 * @property-read int $defaultState
 */
class StateService extends Component implements StateServiceInterface
{
    protected ActiveRecord $record;

    public string $field = 'state';
    public ?string $table = null;

    protected array $states = [];
    /**
     * @var array<string, array<int,int>|array> Can be a simple list of states, a complex where clause, or null for no
     *     default filtering
     * @see StatableQueryTrait::$stateFilterList
     * @see StatableActiveQueryTrait::$stateFilterCondition
     */
    protected array $defaultQueriedStates = [];
    protected static string $translationBase = 'base';

    /**
     * @var StateServiceInterface[]
     */
    protected static array $stateServiceInstances = [];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->initStates();

        $this->trigger(StateServiceInterface::EVENT_INIT);
    }

    /**
     * @throws InvalidConfigException
     */
    public function initStates(): self
    {
        $this->trigger(self::EVENT_INIT_STATES);

        foreach ($this->defaultQueriedStates as $context => &$defaultQueriedStates) {
            if (!is_string($context)) {
                throw new InvalidConfigException(sprintf(
                    "Array keys for %s::%s must be context names (strings)",
                    static::class,
                    '$defaultQueriedStates'
                ));
            }

            if (null !== $defaultQueriedStates && !self::isExtendedStateFilter($defaultQueriedStates)) {
                // make sure only allowed states are filtered for by default
                $this->validateState($defaultQueriedStates, $defaultQueriedStates);
            }
        }
        unset($defaultQueriedStates);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function allowState(int $state, ?string $name = null): self
    {
        if ($name !== null && '' === $name = trim($name)) {
            $name = null;
        }

        if ($name && $state !== (StatableInterface::RESERVED_STATE_NAMES[$name] ?? $state)) {
            throw new InvalidArgumentValueException(
                '$name',
                null,
                $name,
                "$name is a reserved status name. See StatableInterface::RESERVED_STATE_NAMES."
            );
        }

        $name ??= array_search($state, StatableInterface::RESERVED_STATE_NAMES, true);

        if ($name === false) {
            $name = $state;
        }

        $this->states[$name] = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function denyState(int $state): self
    {
        while (false !== $stateIndex = array_search($state, $this->states, true)) {
            unset($this->states[$stateIndex]);
        }

        return $this;
    }

    /**
     * Return default state (first entry in allowed states)
     *
     * @return int
     */
    public function getDefaultState(): int
    {
        return reset($this->states);
    }

    /**
     * @param string|null $filterByState
     * @param array|null $options
     * @param bool $stopOnFirstMatch
     *
     * @return array
     */
    public function getStates(?string $filterByState = null, ?array $options = [], bool $stopOnFirstMatch = false): array
    {
        if ($filterByState === null || trim($filterByState) === '') {
            return $this->states;
        }

        $found = false;

        return array_filter(
            $this->states,
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

    public function getStateContexts(): array
    {
        return array_keys($this->defaultQueriedStates);
    }

    /**
     * @param array $config = ['withDeleted' => true]
     *
     * @return <int, string>[] state-indexed array of translated strings
     */
    public function getStateOptions(array $config = []): array
    {
        return EventWithTypedValue::create()
            ->setAllowedTypes(['array'])
            ->setData($config)
            ->setValue([])
            ->fire(self::EVENT_STATE_OPTIONS, $this)
            ->getValue();
    }

    public function getDefaultQueriedStates(?array $config = null): ?array
    {
        $config ??= [];

        return $this->defaultQueriedStates[$config['filterContext'] ?? FilterableQueryInterface::FILTER_CONTEXT_DEFAULT] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getStateByName($state = null): array
    {
        if ($state === null) {
            return $this->getStates();
        }

        foreach ($this->getStates() as $key => $currentState) {
            if (
                (!is_int($key) && $key === $state)
                || $currentState === filter_var($state, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
            ) {
                return [$key => $state];
            }
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getStateNames($state = null): array
    {
        $states = $this->getStateByName($state);

        foreach ($states as $key => &$currentState) {
            $currentState = Yii::t(static::$translationBase, $key);
        }

        return $states;
    }

    public function isStateInList(string $state, ?array &$states = null): ?int
    {
        $states ??= $this->getStates();

        switch (true) {
            case is_int($saveState = $state):
            case null !== $saveState = filter_var($state, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE):
            case null !== $saveState = $states[$state] ?? null:
            case null !== $saveState = StatableInterface::RESERVED_STATE_NAMES[$state] ?? null:
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
     * Check if the Content has the requested state
     *
     * @param int|string|null $state
     *
     * @return bool
     */
    public function is($state): bool
    {
        // Always convert to integer before comparing,
        // because right after save the content->state may be a string
        return (int)$this->getStateRecord()->{$this->field} === (int)$state;
    }

    /**
     * @inheritdoc
     */
    public function canChange($state, array $options = []): bool
    {
        return in_array((int)$state, $this->states, true);
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return ActiveRecord
     */
    public function getRecord(): ActiveRecord
    {
        return $this->record;
    }

    /**
     * @return ActiveRecord
     */
    public function getStateRecord(): ActiveRecord
    {
        return $this->record instanceof ContentActiveRecord || $this->record instanceof ContentAddonActiveRecord
            ? $this->record->content
            : $this->record;
    }

    /**
     * @param ActiveRecord $record
     * @param array|null $config
     *
     * @return StateService
     */
    public function setRecord(ActiveRecord $record, ?array $config = null): StateService
    {
        $this->record = $record;

        if (!empty($config)) {
            Yii::configure($this, $config);
        }

        $this->table ??= $this->getStateRecord()::tableName();

        $this->trigger(StateServiceInterface::EVENT_SET_RECORD);

        return $this;
    }

    /**
     * Set new state
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function set($state, array $options = []): ?ActiveRecord
    {
        /** @noinspection PhpParamsInspection */
        if (!$this->validateState($state, $state)) {
            return null;
        }

        if (!$this->canChange($state)) {
            return null;
        }

        $record = $this->getStateRecord();

        if ($state === StatableInterface::STATE_SCHEDULED) {
            if (empty($options['scheduled_at'])) {
                return null;
            }

            $record->scheduled_at = $options['scheduled_at'];
            (new DbDateValidator())->validateAttribute($record, 'scheduled_at');
            if ($record->hasErrors('scheduled_at')) {
                $record->scheduled_at = null;
                return null;
            }
        }

        $record->setAttribute($this->field, $state);

        return $record;
    }

    /**
     * Set and save new state for the Content
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function update($state, array $options = []): bool
    {
        return $this->set($state, $options) && $this->getStateRecord()->save();
    }

    /**
     * @inheritdoc
     */
    public function validateState(&$result, $state, ?array $params = [], bool $allowArray = true, bool $throwException = true): bool
    {
        // if states are empty, use the default state instead
        if ($state === null || $state === '') {
            $result = $this->getDefaultState();

            return true;
        }

        $self = $this;
        $availableStates = null;
        $invalidStates = null;
        $result = [];

        /**
         * @param Throwable $error
         *
         * @return false
         * @throws Throwable
         */
        $returnError = static function (Throwable $error) use (&$result, $throwException) {
            if ($throwException) {
                throw $error;
            }

            $result = $error;

            return false;
        };

        $addError = static function ($state, ?string $error = null) use (&$self, &$availableStates, &$invalidStates) {
            $availableStates ??= $self->getStates();

            // check if it's a known state
            if (null !== $stateExists = $self->isStateInList($state, $availableStates)) {
                $name = strtr("{state_name} ({state_id}:{state_key}", [
                    '{state_name}',
                    '{state_id}' => $stateExists,
                    '{state_key}' => array_search($stateExists, $availableStates, true),
                ]);

                $error = Yii::t(
                    self::$translationBase,
                    $error ?? 'The selected state "{state}" is not allowed.',
                    [
                        'state' => $name,
                    ]
                );
            } else {
                $error = Yii::t(
                    static::$translationBase,
                    'The selected state \'{state}\' is unknown.',
                    [
                        'state' => $state,
                    ]
                );
            }

            // add the error to the invalid states list
            $invalidStates[$error] = $state;
        };

        $isSingleState = !is_iterable($state);
        $states = ArrayHelper::toArray($state);
        $countStates = count($states);

        if (!$allowArray && !$isSingleState && $countStates !== 1 && !is_array($x = reset($states))) {
            return $returnError(
                new InvalidArgumentTypeException(
                    sprintf(
                        'If parameter $allowArray is true, argument $state must be %s. State configuration: %s. %s given.',
                        implode(', ', ['iterable', 'array', Traversable::class]),
                        Json::encode($states),
                        get_debug_type($x)
                    )
                )
            );
        }

        if ($state === $this->getStates()) {
            $result = $state;

            return true;
        }

        $allowedStates = $this->getStates(null, $params ??= []);
        $result = [];

        foreach ($states as $index => &$state) {
            $condition = null;
            $countItem = null;

            if (is_array($state)) {
                if (0 === $countItem = count($state)) {
                    $addError($index, 'Invalid state configuration (empty array).');
                    continue;
                }

                if (!is_int($index) || $countStates === 1) {
                    // only a condition may have multiple entries. This is only valid, if one single state is provided
                    $condition = $state;
                    $state = $index;
                } elseif ($countItem === 1 /*&& is_array($condition = reset($state))*/) {
                    $condition = reset($state);
                    $state = key($state);
                } else {
                    $addError($index, 'Invalid state configuration.');
                    continue;
                }
            }

            // check if the currently checked state is allowed
            if (null === $saveState = $this->isStateInList($state, $allowedStates)) {
                if (
                    $condition === null && (is_string($index) || $countStates === 1) && null !== $saveState = $this->isStateInList(
                        $index,
                        $allowedStates
                    )
                ) {
                    $condition = $state;
                    $state = $index;
                } else {
                    // otherwise, let's build the correct error message
                    $addError($state);
                    continue;
                }
            }

            $existing = $result[$saveState] ?? null;

            if ($condition === null) {
                if ($existing !== null) {
                    continue;
                }

                $result[$saveState] = false;
                continue;
            }

            if ($existing === null || $existing === false) {
                $result[$saveState] = $condition;
                continue;
            }

            $or = $existing[0] ?? 'OR';

            if (is_string($or) && strtoupper($or) !== 'OR') {
                $existing = [
                    'OR',
                    $existing
                ];
            }
            $existing[] = $condition;

            $result[$saveState] = $existing;
        }
        unset($state);

        // if there are invalid states, return or throw and exception with the errors
        if ($invalidStates !== null) {
            return $returnError(
                new InvalidStateException(
                    sprintf(
                        'The following states are not allowed for the current model %s: %s. State configuration: %s',
                        static::class,
                        Json::encode($invalidStates),
                        Json::encode($states)
                    ),
                    0,
                    null,
                    $invalidStates,
                    $allowedStates,
                    $params
                )
            );
        }

        if ($isSingleState) {
            reset($result);
            $result = key($result);

            return true;
        }

        foreach ($result as $index => &$state) {
            if ($state === false) {
                $state = $index;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function factory($model, array $config = []): StateServiceInterface
    {
        if ($model instanceof StatableInterface) {
            $class = get_class($model);
        } elseif (!is_string($model)) {
            throw new InvalidArgumentTypeException(
                '$model',
                ['string', StatableInterface::class],
                $model
            );
        } elseif (!is_subclass_of($model, StatableInterface::class)) {
            throw new InvalidConfigException(sprintf(
                "String '$model' is not a class name implementing %s",
                StatableInterface::class
            ));
        } else {
            $class = $model;
            $model = null;
        }


        /** @var StateServiceInterface $service */
        $service = static::$stateServiceInstances[$class]
            ??= Yii::createObject(array_merge(['class' => static::class], $config));

        if ($model !== null) {
            Helpers::checkType($model, [ActiveRecord::class], null, '$model');

            $service = clone($service);
            $service->setRecord($model, $config);
        }

        return $service;
    }

    /**
     * @param array|null $queriedStates
     *
     * @return bool
     */
    public static function isExtendedStateFilter(?array &$queriedStates): bool
    {
        return $queriedStates !== null
            && ($first = $queriedStates[0] ?? null)
            && is_string($first)
            && in_array(strtolower($first), ['or', 'and']);
    }
}
