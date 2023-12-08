<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\components\StatableActiveQuery;
use humhub\exceptions\InvalidStateException;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\validators\InlineValidator;

/**
 * @since 1.16
 */
interface StatableInterface
{
    /**
     * @event Event is used when a Content state is changed.
     */
    public const EVENT_STATE_CHANGED = 'changedState';

    /**
     * Record States
     */
    public const STATE_ARCHIVED = 2;
    public const STATE_INVALID = 9;
    public const STATE_DISABLED = 0;
    public const STATE_ENABLED = 1;
    public const STATE_PUBLISHED = 1;
    public const STATE_NEEDS_APPROVAL = 2;
    public const STATE_DRAFT = 10;
    public const STATE_SCHEDULED = 20;
    public const STATE_DELETED = 100;

    /**
     * @deprecated USE static::STATE_DISABLED instead
     */
    public const STATUS_DISABLED = self::STATE_DISABLED;

    /**
     * @deprecated USE static::STATE_DELETED instead
     */
    public const STATUS_SOFT_DELETED = self::STATE_DELETED;

    /**
     * @deprecated USE static::STATE_SOFT_DELETED instead
     */
    public const STATE_SOFT_DELETED = 3; //self::STATE_DELETED;

    /**
     * @deprecated USE static::STATE_NEEDS_APPROVAL instead
     */
    public const STATUS_NEED_APPROVAL = self::STATE_NEEDS_APPROVAL;

    /**
     * @deprecated USE static::STATE_ENABLED instead
     */
    public const STATUS_ENABLED = self::STATE_ENABLED;

    /**
     * @deprecated USE self::STATE_ARCHIVED instead
     */
    public const STATUS_ARCHIVED = self::STATE_ARCHIVED;

    /**
     * Content States - By default, only content with the "Published" state is returned.
     *
     * @const array<string,int>
     */
    public const RESERVED_STATE_NAMES
        = [
            'archived' => self::STATE_ARCHIVED,
            'deleted' => self::STATE_DELETED,
            'disabled' => self::STATE_DISABLED,
            'draft' => self::STATE_DRAFT,
            'enabled' => self::STATE_ENABLED,
            'needs approval' => self::STATE_NEEDS_APPROVAL,
            'published' => self::STATE_PUBLISHED,
            'scheduled' => self::STATE_SCHEDULED,
            'softDeleted' => self::STATE_SOFT_DELETED,
        ];

    /**
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     *
     * @return bool
     * @since 1.16
     */
    public function canChangeState($state, array $options = []): bool;

    /**
     * @param string $attribute the attribute currently being validated
     * @param mixed $params the value of the "params" given in the rule
     * @param InlineValidator $validator related InlineValidator instance.
     * @param mixed $current the currently validated value of attribute.
     *
     * @return bool
     * @since 1.16
     */
    public function validateStateAttribute(string $attribute, $params, InlineValidator $validator, $current): bool;


    public function getStateService(): StateServiceInterface;

    /**
     * @return string|StateServiceInterface
     */
    public static function getStateServiceClass(): string;

    public static function getStateServiceTemplate(): StateServiceInterface;

    public static function getQueryDefaultStates(?array $config = null): ?array;

    /**
     * @param int[]|InvalidStateException|InvalidArgumentException $result Out parameter with the normalized
     *                                                                             $state, if the function returns
     *                                                                             true, or the error message
     *                                                                             otherwise.
     * @param int|array|null $state
     * @param array $params Parameters used for
     *                                                                             getAllowedStates()
     * @param bool $allowArray Determines if $state may be an array
     *                                                                             of states.
     * @param bool $throwException Determines if an Exception shall be
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
}
