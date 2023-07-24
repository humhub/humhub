<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\exceptions\InvalidStateException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * This service is used to extend Content record for state features
 *
 * @since 1.14
 */
interface StateServiceInterface
{
    public const EVENT_INIT = 'init';
    public const EVENT_INIT_STATES = 'setInitStates';
    public const EVENT_STATE_OPTIONS = 'getStateOptions';
    public const EVENT_SET_RECORD = 'setRecord';

    /**
     * Function is called upon object initialisation (static::init()) and
     * 1. SHOULD set up the allowed states
     * 2. CAN define the default queried states
     * 3. MUST call the static::EVENT_INIT_STATES event
     *
     * @see static::EVENT_INIT_STATES
     */
    public function initStates(): StateServiceInterface;

    /**
     * Allow a state for the Content
     *
     * @param int $state
     */
    public function allowState(int $state): StateServiceInterface;

    /**
     * Exclude a state from the allowed list
     *
     * @param int $state
     */
    public function denyState(int $state): StateServiceInterface;

    public function getField(): string;

    /**
     *
     * @param null|int|string $filterByState Used to check if a given state is available. Set to null to get all
     *                                       available states (subject to the moderation by the $options parameter).
     * @param array           $options       Allowed states can be moderated by the $options parameter
     *
     * @return array
     */
    public function getStates(?string $filterByState = null, array $options = []): array;

    public function getStateContexts(): array;
    public function getDefaultQueriedStates(?array $config = null): ?array;

    /**
     * @param int|string|null $state State ID or key-word, or null to get all
     *
     * @return array
     */
    public function getStateByName($state = null): array;

    /**
     * Get translated names of the states
     *
     * @param int|string|null $state
     *
     * @return array
     */
    public function getStateNames($state = null): array;


    /**
     * Check if the Content has the requested state
     *
     * @param int|string|null $state
     *
     * @return bool
     */
    public function is($state): bool;

    /**
     * Check if the requested state can be set to the Content
     *
     * @param int|string|null $state
     * @param array           $options Additional options depending on state
     *
     * @return bool
     */
    public function canChange($state, array $options = []): bool;

    /**
     * @param ActiveRecord $record
     *
     * @return StateServiceInterface
     */
    public function setRecord(ActiveRecord $record, ?array $config = null): StateServiceInterface;

    /**
     * @return ActiveRecord
     */
    public function getRecord(): ActiveRecord;

    /**
     * @param array $config
     *
     * @return <int, string>[] state-indexed array of translated strings
     */
    public function getStateOptions(array $config = []): array;

    /**
     * Set new state
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function set($state, array $options = []): ?ActiveRecord;

    /**
     * Set and save new state for the Content
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function update($state, array $options = []): bool;

    /**
     * @param int[]|array|InvalidStateException|InvalidArgumentException $result Out parameter with the normalized $state, if
     *        the function returns true, or the error message otherwise.
     * @param int|string|array|null $state State to be validated. It's format depends on the setting of $allowArray:
     *        If the latter is set to false, then it can be either a single state (numeric value or its name) or an array with a
     *        single state as the key and array of conditions as the value.
     *        If $allowArray is set to true, then in addition to the above, the array is allowed to have multiple elements. However,
     *        in this case, all status must be given as array values. If a status is bound to a condition, both must be provided
     *        within an array with exactly one element having the status as key and an array of conditions as value.
     * @param array|null $params Parameters used for getAllowedStates()
     * @param bool $allowArray Determines if $state may be an array of states.
     * @param bool $throwException Determines if an Exception shall be thrown upon error, or just false returned
     *
     * @return bool
     * @throws InvalidStateException
     * @throws InvalidArgumentException
     */
    public function validateState(&$result, $state, ?array $params = [], bool $allowArray = false, bool $throwException = true): bool;

    /**
     * @param string|StatableInterface $model
     * @param array $config
     *
     * @return StateServiceInterface
     * @throws InvalidConfigException
     */
    public static function factory($model, array $config = []): StateServiceInterface;
}
