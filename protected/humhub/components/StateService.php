<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidStateException;
use humhub\interfaces\StatableInterface;
use humhub\interfaces\StateServiceInterface;
use humhub\libs\DbDateValidator;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use Throwable;
use Traversable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This service is used to extend Content record for state features
 *
 * @since 1.15
 *
 * @property-read ActiveRecord $stateRecord
 * @property-read int $defaultState
 */
abstract class StateService extends Component implements StateServiceInterface
{
    public const EVENT_INIT = 'init';
    public const EVENT_SET_RECORD = 'set_record';

    protected ActiveRecord $record;

    public string $field = 'state';
    public ?string $table = null;

    protected array $states = [];
    protected ?array $defaultQueriedStates = null;
    protected static string $translationBase = 'base';

    /**
     * @var StateServiceInterface[]
     */
    protected static array $stateServiceInstances = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->initStates();

        $this->trigger(self::EVENT_INIT);
    }

    public function initStates(): self
    {
        $this->trigger(self::EVENT_INIT_STATES);

        if ($this->defaultQueriedStates !== null) {
            // make sure only allowed states are filtered for by default
            $this->defaultQueriedStates = array_intersect($this->defaultQueriedStates, $this->states);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function allowState(int $state): self
    {
        $this->states[] = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function denyState(int $state): self
    {
        $stateIndex = array_search($state, $this->states, true);
        if ($stateIndex !== false) {
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
    public function getStates(
        ?string $filterByState = null,
        ?array $options = [],
        bool $stopOnFirstMatch = false
    ): array {
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

    public function getDefaultQueriedStates(): ?array
    {
        return $this->defaultQueriedStates;
    }

    /**
     * @inheritdoc
     */
    public function getStateByName($state = null): array
    {
        $found = false;

        return array_filter(
            $this->getStates(),
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
    public function getStateNames($state = null): array
    {
        $states = $this->getStateByName($state);

        array_walk(
            $states,
            static function (
                &$currentState,
                $key,
                $translationBase
            ) {
                $currentState = Yii::t($translationBase, $key);
            },
            static::$translationBase
        );

        return $states;
    }

    public function isStateInList(string $state, ?array &$states = null): ?int
    {
        $states ??= $this->getStates();

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

        $this->trigger(self::EVENT_SET_RECORD);

        return $this;
    }

    /**
     * Set new state
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function set($state, array $options = []): bool
    {
        $state = (int)$state;

        if (!$this->canChange($state)) {
            return false;
        }

        $record = $this->getStateRecord();

        if ($state === StatableInterface::STATE_SCHEDULED) {
            if (empty($options['scheduled_at'])) {
                return false;
            }

            $record->scheduled_at = $options['scheduled_at'];
            (new DbDateValidator())->validateAttribute($record, 'scheduled_at');
            if ($record->hasErrors('scheduled_at')) {
                $record->scheduled_at = null;
                return false;
            }
        }

        $record->{$this->field} = $state;
        return true;
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
    public function validateState(
        &$result,
        $state,
        ?array $params = [],
        bool $allowArray = false,
        bool $throwException = true
    ): bool {
        static $returnError;

        // if states are empty, use the default state instead
        if ($state === null || $state === '') {
            $result = $this->getDefaultState();

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

        if ($state === $this->getStates()) {
            $result = $state;

            return true;
        }

        $availableStates = null;
        $allowedStates = $this->getStates(null, $params ??= []);
        $invalidStates = null;
        $isSingleState = !is_iterable($state);
        $states = ArrayHelper::toArray($state);

        foreach ($states as &$state) {
            if (null !== $saveState = $this->isStateInList($state, $allowedStates)) {
                $state = $saveState;

                continue;
            }

            $availableStates ??= $this->getStates();

            if (null !== $stateExists = $this->isStateInList($state, $availableStates)) {
                $name = strtr("{state_name} ({state_id}:{state_key}", [
                    '{state_name}',
                    '{state_id}' => $stateExists,
                    '{state_key}' => array_search($stateExists, $availableStates, true),
                ]);

                $error = Yii::t(
                    self::$translationBase,
                    'The selected state "{state}" is not valid.',
                    [
                        'state' => $name,
                    ]
                );
            } else {
                $error = Yii::t(
                    static::$translationBase,
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

    /**
     * @inheritdoc
     */
    public static function factory($model, array $config = []): StateServiceInterface
    {
        if ($model instanceof StatableInterface) {
            $class = get_class($model);
        } elseif (!is_string($model)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$model'],
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
        $service = static::$stateServiceInstances[$class] ??= new static($config);

        if ($model !== null) {
            if (!$model instanceof ActiveRecord) {
                throw new InvalidArgumentTypeException(
                    __METHOD__,
                    [1 => '$model'],
                    [ActiveRecord::class],
                    $model
                );
            }

            $service = clone($service);
            $service->setRecord($model, $config);
        }

        return $service;
    }
}
