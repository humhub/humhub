<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\exceptions\InvalidStateException;
use humhub\libs\StatableActiveQuery;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
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
    public const STATE_DISABLED = 0;
    public const STATE_ENABLED = 1;
    public const STATE_PUBLISHED = 1;
    public const STATE_NEEDS_APPROVAL = 2;
    public const STATE_SOFT_DELETED = 3;
    public const STATE_DRAFT = 10;
    public const STATE_SCHEDULED = 20;
    public const STATE_DELETED = 100;

    /**
     * Content States - By default, only content with the "Published" state is returned.
     *
     * @const array<string,int>
     */
    public const RESERVED_STATE_NAMES
        = [
            'deleted' => StatableInterface::STATE_DELETED,
            'disabled' => StatableInterface::STATE_DISABLED,
            'draft' => StatableInterface::STATE_DRAFT,
            'enabled' => StatableInterface::STATE_ENABLED,
            'needsApproval' => StatableInterface::STATE_NEEDS_APPROVAL,
            'published' => StatableInterface::STATE_PUBLISHED,
            'scheduled' => StatableInterface::STATE_SCHEDULED,
            'softDeleted' => StatableInterface::STATE_SOFT_DELETED,
        ];

    /**
     * @param int|string|null $state
     * @param array           $options Additional options depending on state
     *
     * @return bool
     * @since 1.14
     */
    public function canChangeState($state, array $options = []): bool;

    /**
     * @param string          $attribute the attribute currently being validated
     * @param mixed           $params    the value of the "params" given in the rule
     * @param InlineValidator $validator related InlineValidator instance.
     * @param mixed           $current   the currently validated value of attribute.
     *
     * @return bool
     * @since 1.15
     */
    public function validateStateAttribute(string $attribute, $params, InlineValidator $validator, $current): bool;


    public function getStateService(): StateServiceInterface;

    /**
     * @return string|StateServiceInterface
     */
    public static function getStateServiceClass(): string;
    public static function getStateServiceTemplate(): StateServiceInterface;

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
    public static function validateState(&$result, $state, array $params = [], bool $allowArray = false, bool $throwException = true): bool;

    /**
     * @return ActiveQuery|ActiveQueryInterface|StatableActiveQuery|StatableActiveQueryInterface
     */
    public static function find();

    /**
     * @param mixed $condition primary key value or a set of column values
     * @param $allowedStates
     *
     * @return null|ActiveRecordInterface|StatableInterface
     */
    public static function findOne($condition, $allowedStates = null);

    /**
     * @param mixed $condition primary key value or a set of column values
     * @param $allowedStates
     *
     * @return static[]|ActiveRecordInterface[]|StatableInterface[]
     */
    public static function findAll($condition, $allowedStates = null);
}
