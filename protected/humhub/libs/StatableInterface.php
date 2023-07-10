<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidStateException;
use yii\base\InvalidArgumentException;
use yii\validators\InlineValidator;

/**
 *
 *
 *
 * @const array<string,int> STATES_AVAILABLE Available States. The first entry is the default, if no state is given on
 *        the record.
 * @since 1.15
 */
interface StatableInterface
{
    /**
     * Record States - By default, only content with the "Published" state is returned.
     */
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;
    public const STATE_PUBLISHED = 1;
    public const STATUS_NEED_APPROVAL = 2;
    public const STATUS_SOFT_DELETED = 3;
    public const STATE_DRAFT = 10;
    public const STATE_SCHEDULED = 20;
    public const STATE_DELETED = 100;

    /**
     * @param int|string|null $state
     * @param array           $options Additional options depending on state
     *
     * @since 1.14
     */
    public function setState(
        $state,
        array $options = []
    );

    /**
     * @param int|string|null $state
     * @param array           $options Additional options depending on state
     *
     * @return bool
     * @since 1.14
     */
    public function canChangeState(
        $state,
        array $options = []
    ): bool;

    /**
     * @param string          $attribute the attribute currently being validated
     * @param mixed           $params    the value of the "params" given in the rule
     * @param InlineValidator $validator related InlineValidator instance.
     * @param mixed           $current   the currently validated value of attribute.
     *
     * @return bool
     * @since 1.15
     */
    public function validateStateAttribute(
        string $attribute,
        $params,
        InlineValidator $validator,
        $current
    ): bool;

    /**
     *
     * @param null|int|string $filterByState Used to check if a given state is available. Set to null to get all
     *                                       available states (subject to the moderation by the $options parameter).
     * @param array           $options       Allowed states can be moderated by the $options parameter
     *
     * @return array
     */
    public static function getAllowedStates(
        ?string $filterByState = null,
        array $options = []
    ): array;

    /**
     * @param int|string|null $state State ID or key-word, or null to get all
     *
     * @return array
     */
    public static function getStateByName($state = null): array;

    /**
     * Get translated names of the states
     *
     * @param int|string|null $state
     *
     * @return array
     */
    public static function getStateNames($state = null): array;

    /**
     * @param int[]|InvalidStateException|InvalidArgumentException $result         Out parameter with the normalized
     *                                                                             $state, if the function returns
     *                                                                             true, or the error message
     *                                                                             otherwise.
     * @param int|array|null                                       $state
     * @param array                                                $params         Parameters used for
     *                                                                             getAllowedStates()
     * @param bool                                                 $allowArray     Determines if $state may be an array
     *                                                                             of states.
     * @param bool                                                 $throwException Determines if an Exception shall be
     *                                                                             thrown upon error, or just false
     *                                                                             returned
     *
     * @return bool
     * @throws InvalidStateException|InvalidArgumentException
     */
    public static function validateState(
        &$result,
        $state,
        array $params = [],
        bool $allowArray = false,
        bool $throwException = true
    ): bool;

    public static function findOne($condition, $allowedStates = null);

    public static function findAll($condition, $allowedStates = null);
}
