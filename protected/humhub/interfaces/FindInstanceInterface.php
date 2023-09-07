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
interface FindInstanceInterface
{
    /**
     * Returns a specific instance of the class identified by the PK (or unique identifier in some instances). If the
     * record cannot be found, null is returned. Special inout is null, where a default instance may be returned.
     *
     * @param self|int|string|array|null $identifier Object identifying information. Can be an instance of self (which
     *        will be simply returned), a one-dimensional PK as int or string, or an array of pk information.
     *        If null, the default instance (e.g., active User) would be returned.
     * @param null|array $config = [
     *     'cached' => true,                    // use cached results
     *     'onEmpty' => null,                   // if provided, use this value in case of empty $identifier
     *     'exceptionMessageSuffix' => '',      // message used to append to the InvalidArgumentTypeException
     *     'intKey' => 'id',
     *     'stringKey' => string,               // If provided, this key will be used to look up string keys, e.g. 'guid'
     *     ]
     * @param iterable|null $simpleCondition    // ['field' => 'value']-filter to be applied on the resulting object,
     *                                             otherwise return $config['onEmpty'] or null
     *
     * @return static|null
     *
     * @throws InvalidArgumentTypeException
     */
    public static function findInstance($identifier, ?array $config = [], ?iterable $simpleCondition = null): ?self;

    /**
     * @see static::findInstance()
     *
     * @return int|array|null (Return type MUST be ?int or ?array on the implementing function!)
     *
     * @throws InvalidArgumentTypeException
     */
    public static function findInstanceAsId($identifier, array $config = []);
}
