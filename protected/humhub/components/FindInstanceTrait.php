<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\exceptions\InvalidArgumentException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\helpers\RuntimeCacheHelper;
use humhub\interfaces\FindInstanceInterface;
use Yii;
use yii\base\ErrorException;

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
     * @inheritdoc
     * @return static|null
     * @throws InvalidArgumentTypeException
     * @see FindInstanceInterface::findInstance
     */
    public static function findInstance($identifier, ?iterable $simpleCondition = null): ?self
    {
        if (!RuntimeCacheHelper::isSimpleCondition($simpleCondition, true)) {
            throw new InvalidArgumentException(__METHOD__, [3 => '$simpleCondition'], ['array', 'null'], $simpleCondition, 'array keys must be strings and values must be scalars');
        }

        if (!RuntimeCacheHelper::isSimpleCondition($simpleCondition, true)) {
            throw new InvalidArgumentException(__METHOD__, [3 => '$simpleCondition'], ['array', 'null'], $simpleCondition, 'array keys must be strings and values must be scalars');
        }

        // check if the given $identifier is already an instance of the required class ...
        if ($identifier instanceof static) {
            // ... then return it, if it matches the $simpleCondition
            return static::matchProperties($identifier, $simpleCondition);
        }

        $errorIdentifier = $identifier;

        if (static::validateInstanceIdentifier($identifier) <= self::INSTANCE_IDENTIFIER_IS_SELF) {
            throw new InvalidArgumentTypeException(
                str_replace(__CLASS__, static::class, __METHOD__),
                [1 => '$identifier'],
                [static::class, 'int', '(int)string', 'string', 'array'],
                $errorIdentifier
            );
        }

        // validate $identifier and build search ?array $criteria
        if (is_scalar($identifier)) {
            $identifier = (array)$identifier;
        }

        if (is_int(key($identifier))) {
            $criteria = is_subclass_of(static::class, \yii\db\ActiveRecord::class, true) ? static::primaryKey() : ['id'];

            if (count($criteria) !== count($identifier)) {
                throw new InvalidArgumentException(
                    str_replace(__CLASS__, static::class, __METHOD__),
                    [1 => '$identifier'],
                    $criteria,
                    $identifier,
                    ' invalid number of arguments for primary key'
                );
            }

            $criteria = array_combine($criteria, $identifier);
        } else {
            $criteria = $identifier;
        }

        // normalise $criteria to cache identifier
        $cacheKey = RuntimeCacheHelper::normaliseObjectIdentifier(static::class, $criteria);

        // try to get the record from cache
        $record = Yii::$app->runtimeCache->get($cacheKey);

        if ($record === false) {
            // otherwise, look it up in the database and save it into/update the cache
            $record = static::find()
                ->where($criteria)
                ->limit(2)
                ->all();

            switch (count($record)) {
                case 0:
                    $record = null;
                    break;

                case 1:
                    $record = reset($record);
                    break;

                default:
                    throw new InvalidArgumentException(
                        __METHOD__,
                        [1 => '$identifier'],
                        ['unique record identifier'],
                        $identifier,
                        'Invalid number of records (2 or more) returned! Query must return 0 or 1 record only.'
                    );
            }

            RuntimeCacheHelper::set($record, $cacheKey);
        }

        // ... then return it, if it matches the $simpleCondition
        return static::matchProperties($record, $simpleCondition);
    }

    public static function findInstanceAsId($identifier, ?iterable $simpleCondition = null): ?int
    {
        return static::findInstance($identifier, $simpleCondition)->id ?? null;
    }

    public function afterDelete()
    {
        RuntimeCacheHelper::deleteByInstance($this);

        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        RuntimeCacheHelper::deleteByInstance($this);

        parent::afterSave($insert, $changedAttributes);
    }

    public static function deleteAll($condition = null, $params = [])
    {
        RuntimeCacheHelper::deleteByClass(static::class, $condition);

        return parent::deleteAll($condition, $params);
    }

    public static function updateAll($attributes, $condition = '', $params = [])
    {
        RuntimeCacheHelper::deleteByClass(static::class, $condition);

        return parent::updateAll($attributes, $condition, $params);
    }

    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        RuntimeCacheHelper::deleteByClass(static::class, $condition);

        return parent::updateAllCounters($counters, $condition, $params);
    }

    /**
     * @inheritdoc
     */
    public function getUniqueIdVariants(?array $keys = null): ?array
    {
        return RuntimeCacheHelper::buildUniqueIDs($this, $keys, false, false);
    }

    /**
     * @param static|int|string|array|null $identifier
     */
    protected static function validateInstanceIdentifier(&$identifier, ?string $stringKey = null): int
    {
        if ($identifier === null) {
            return self::INSTANCE_IDENTIFIER_IS_NULL;
        }

        if ($identifier instanceof static) {
            return self::INSTANCE_IDENTIFIER_IS_SELF;
        }

        if (is_int($identifier)) {
            return self::INSTANCE_IDENTIFIER_IS_INT;
        }

        if (is_string($identifier)) {
            $identifier = trim($identifier);

            if ($identifier === '') {
                $identifier = null;

                return self::INSTANCE_IDENTIFIER_IS_NULL;
            }

            $id = filter_var($identifier, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($id !== null) {
                $identifier = $id;

                return self::INSTANCE_IDENTIFIER_IS_INT;
            }

            if ($stringKey) {
                $identifier = [$stringKey => $identifier];

                return self::INSTANCE_IDENTIFIER_IS_ARRAY;
            }

            return self::INSTANCE_IDENTIFIER_IS_STRING;
        }

        if (is_array($identifier)) {
            if (count($identifier) === 0) {
                $identifier = null;

                return self::INSTANCE_IDENTIFIER_IS_NULL;
            }

            return self::INSTANCE_IDENTIFIER_IS_ARRAY;
        }

        return self::INSTANCE_IDENTIFIER_INVALID;
    }


    /**
     * @param $identifier
     * @param iterable|null $simpleCondition
     * @param $onEmpty
     *
     * @return mixed|null
     * @noinspection ReferencingObjectsInspection
     */
    private static function matchProperties($identifier, ?iterable &$simpleCondition, $onEmpty = null)
    {
        if (!is_object($identifier)) {
            return $identifier;
        }

        if ($simpleCondition === null) {
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
