<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\interfaces\FindInstanceInterface;
use Throwable;
use Yii;

/**
 * Helper trait for implementing FindInstanceInterface
 *
 * @since 1.15
 * @see FindInstanceInterface
 */
trait FindInstanceTrait
{
    public static function find()
    {
        return Yii::createObject(CacheableActiveQuery::class, [static::class]);
    }

    /**
     * @throws InvalidArgumentTypeException
     * @see FindInstanceInterface::findInstance
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected static function findInstanceHelper($identifier, ?array $config = [], iterable $simpleCondition = []): ?self
    {
        $config ??= [];

        // check if the given $identifier is already an instance of the required class ...
        if ($identifier instanceof static) {
            // ... then return it, if it matches the $simpleCondition
            return static::matchProperties($identifier, $simpleCondition, $config['onEmpty'] ?? null);
        }

        if (is_string($identifier)) {
            $identifier = trim($identifier);
        }

        // check if the $identifier is empty (0 or '0' are NOT considered empty as they denote a valid integer key)
        if (empty($identifier) && $identifier !== 0 && $identifier !== '0') {
            if (array_key_exists('onEmpty', $config)) {
                return $config['onEmpty'];
            }

            throw new InvalidArgumentTypeException(
                str_replace(__CLASS__, static::class, __METHOD__),
                [1 => '$identifier'],
                [self::class, 'int', ($config['stringKey'] ?? null) === null ? '(int)string' : 'string'],
                $identifier,
                false,
                $config['exceptionMessageSuffix'] ?? ''
            );
        }

        // validate $identifier and build search ?array $criteria
        if (
            // ... for being an integer (or convertable to one)
            is_int($id = $identifier)
            || null !== $id = filter_var($identifier, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
        ) {
            $criteria = [$config['intKey'] ?? 'id' => $id];
        } elseif (is_string($identifier) && ($stringKey = $config['stringKey'] ?? null)) {
            // ... for being a string
            $criteria = [$stringKey => $identifier];
        } elseif (is_array($identifier)) {
            // ... an array
            $criteria = $identifier;
        } else {
            // or invalid.
            $criteria = null;
        }

        if ($criteria) {
            // normalise $criteria to cache identifier
            $cacheKey = CacheableActiveQuery::normaliseObjectIdentifier(static::class, $criteria);

            /**
             * Callback function used to find the record in the database if it's not found in the cache
             *
             * @return self|null
             */
            $find = static fn(): ?self => static::find()->where($criteria)->one();

            if ($config['cached'] ?? true) {
                // unless cache-lookup is prevented, try to get it from cache
                $identifier = Yii::$app->runtimeCache->getOrSet($cacheKey, $find);
            } else {
                // otherwise, look it up in the database and save it into/update the cache
                $identifier = $find();
                Yii::$app->runtimeCache->set($cacheKey, $identifier);
            }

            if ($identifier) {
                $cacheKey = [$cacheKey];
                CacheableActiveQuery::cacheProcessVariants('set', $identifier, null, $cacheKey);
            }

            // ... then return it, if it matches the $simpleCondition
            return static::matchProperties($identifier, $simpleCondition, $config['onEmpty'] ?? null);
        }

        throw new InvalidArgumentTypeException(
            str_replace(__CLASS__, static::class, __METHOD__),
            [1 => '$identifier'],
            [self::class, 'int', ($config['stringKey'] ?? null) === null ? '(int)string' : 'string'],
            $identifier,
            array_key_exists('onEmpty', $config),
            $config['exceptionMessageSuffix'] ?? ''
        );
    }

    public static function findInstanceAsId($identifier, array $config = []): ?int
    {
        return static::findInstance($identifier, $config)->id ?? null;
    }

    public function afterDelete()
    {
        CacheableActiveQuery::cacheProcessVariants('delete', $this);

        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        CacheableActiveQuery::cacheProcessVariants('delete', $this);

        parent::afterSave($insert, $changedAttributes);
    }

    public static function deleteAll($condition = null, $params = [])
    {
        CacheableActiveQuery::cacheDeleteByClass(static::class, $condition);

        return parent::deleteAll($condition, $params);
    }

    public static function updateAll($attributes, $condition = '', $params = [])
    {
        CacheableActiveQuery::cacheDeleteByClass(static::class, $condition);

        return parent::updateAll($attributes, $condition, $params);
    }

    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        CacheableActiveQuery::cacheDeleteByClass(static::class, $condition);

        return parent::updateAllCounters($counters, $condition, $params);
    }

    /** @noinspection ReferencingObjectsInspection */
    private static function matchProperties($identifier, iterable &$simpleCondition, $onEmpty = null)
    {
        if (!is_object($identifier)) {
            return $identifier;
        }

        foreach ($simpleCondition as $field => $value) {
            if ($identifier->$field != $value) {
                return $onEmpty;
            }
        }

        return $identifier;
    }
}
