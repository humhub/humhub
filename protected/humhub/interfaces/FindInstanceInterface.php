<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidConfigTypeException;

/**
 * @since 1.15
 */
interface FindInstanceInterface extends UniqueIdentifiersInterface
{
    public const INSTANCE_IDENTIFIER_INVALID = 0;
    public const INSTANCE_IDENTIFIER_IS_NULL = 1;
    public const INSTANCE_IDENTIFIER_IS_SELF = 2;
    public const INSTANCE_IDENTIFIER_IS_INT = 4;
    public const INSTANCE_IDENTIFIER_IS_STRING = 8;
    public const INSTANCE_IDENTIFIER_IS_ARRAY = 16;

    /**
     * Returns a specific instance of the class identified by the PK (or unique identifier in some instances). If the
     * record cannot be found, null is returned. Special inout is null, where a default instance may be returned.
     *
     * @param self|int|string|array|null $identifier Object identifying information. Can be an instance of self (which
     *        will be simply returned), a one-dimensional PK as int or string, or an array of pk information.
     *        If null, the default instance (e.g., active User) would be returned.
     * @param iterable|null $simpleCondition    // ['field' => 'value']-filter to be applied on the resulting object,
     *                                             otherwise return $config['onEmpty'] or null
     *
     * @return static|null
     *
     * @throws InvalidArgumentTypeException
     */
    public static function findInstance($identifier, ?iterable $simpleCondition = null): ?self;

    /**
     * @see static::findInstance()
     *
     * @return int|array|null (Return type MUST be ?int or ?array on the implementing function!)
     *
     * @throws InvalidArgumentTypeException
     */
    public static function findInstanceAsId($identifier, ?iterable $simpleCondition = null);
}
