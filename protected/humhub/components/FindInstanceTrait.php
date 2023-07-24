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
     * @param self|int|string|null $identifier User or User ID. Null for current user
     * @param array $config = [
     *     'cached' => true,                    // use cached results
     *     'onEmpty' => null,                   // if provided, use this value in case of empty $identifier
     *     'exceptionMessageSuffix' => '',      // message used to append to the InvalidArgumentTypeException
     *     'intKey' => 'id',
     *     'stringKey' => string,               // If provided, this key will be used to look up string keys, e.g. 'guid'
     *     'exception' => Throwable,            // throw this exception rather than InvalidArgumentTypeException
     *     ]
     * @param iterable $simpleCondition = [ 'field' => 'value' ] // filter to ble applied on the resulting record, otherwise return 'onEmpty'
     *
     * @return self|null
     *
     * @throws InvalidArgumentTypeException|InvalidConfigTypeException
     * @see FindInstanceInterface::findInstance
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected static function findInstanceHelper($identifier, ?array $config = [], iterable $simpleCondition = []): ?self
    {
        $filter = static function ($identifier) use (&$config, &$simpleCondition) {
            if (is_object($identifier)) {
                foreach ($simpleCondition as $field => $value) {
                    if ($identifier->$field != $value) {
                        return $config['onEmpty'] ?? null;
                    }
                }
            }
            return $identifier;
        };

        $config ??= [];

        if ($identifier instanceof static) {
            return $filter($identifier);
        }

        if (is_string($identifier)) {
            $identifier = trim($identifier);
        }

        if (array_key_exists('onEmpty', $config) && empty($identifier) && $identifier !== 0 && $identifier !== '0') {
            return $config['onEmpty'];
        }

        if (
            is_int($id = $identifier)
            || null !== $id = filter_var($identifier, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
        ) {
            $criteria = [$config['intKey'] ?? 'id' => $id];
        } elseif (is_string($identifier) && ($stringKey = $config['stringKey'] ?? null)) {
            $criteria = [$stringKey => $identifier];
        } elseif (is_array($identifier)) {
            $criteria = $identifier;
        } else {
            $criteria = null;
        }

        if ($criteria) {
            /**
             * @return self|array|null
             */
            $find = static fn(): ?self => static::find()->where($criteria)->one();

            if ($config['cached'] ?? true) {
                $identifier = Yii::$app->runtimeCache->getOrSet(RuntimeBaseCache::normaliseObjectIdentifier(static::class, $criteria), $find);
            } else {
                $identifier = $find();
                Yii::$app->runtimeCache->set(RuntimeBaseCache::normaliseObjectIdentifier(static::class, $criteria), $identifier);
            }

            return $filter($identifier);
        }

        // error handling

        $exception = $config['exception'] ?? null;

        if ($exception instanceof Throwable) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $exception;
        }

        $x = new InvalidArgumentTypeException(
            str_replace(__CLASS__, static::class, __METHOD__),
            [1 => '$identifier'],
            [self::class, 'int', ($config['stringKey'] ?? null) === null ? '(int)string' : 'string'],
            $identifier,
            array_key_exists('onEmpty', $config),
            $config['exceptionMessageSuffix'] ?? ''
        );

        if ($exception === null) {
            throw $x;
        }

        if (!is_string($exception)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new InvalidConfigTypeException(
                __METHOD__,
                "exception",
                [Throwable::class],
                $exception,
                true,
                '',
                0,
                $x
            );
        }

        if (is_subclass_of($exception, Throwable::class)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new $exception();
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        throw new InvalidConfigTypeException(
            __METHOD__,
            "exception",
            [Throwable::class],
            $exception,
            true,
            '',
            0,
            $x
        );
    }

    public static function findInstanceAsId($identifier, array $config = []): ?int
    {
        return static::findInstance($identifier, $config)->id ?? null;
    }

    public function hasOneCached(string $class, array $condition, array $identifiers = [])
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $trace = array_column($trace, 'function');

        if ($trace[2] === 'getRelation') {
            return $this->hasOne($class, $condition);
        }

        $fields = array_flip($condition);

        foreach ($fields as $field => &$value) {
            $value = $this->$field;
        }
        unset($value);

        $identifier = RuntimeBaseCache::normaliseObjectIdentifier($class, $identifiers + $fields, $idUsed);

        return (!$idUsed || empty($record = Yii::$app->runtimeCache->get($identifier)))
            ? $this->hasOne($class, $condition)
            : $record;
    }

    public function afterDelete()
    {
        Yii::$app->runtimeCache->delete($this);

        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->runtimeCache->delete($this);

        parent::afterSave($insert, $changedAttributes);
    }

    public static function deleteAll($condition = null, $params = [])
    {
        RuntimeBaseCache::cacheDeleteByClass(static::class, $condition);

        return parent::deleteAll($condition, $params);
    }

    public static function updateAll($attributes, $condition = '', $params = [])
    {
        RuntimeBaseCache::cacheDeleteByClass(static::class, $condition);

        return parent::updateAll($attributes, $condition, $params);
    }

    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        RuntimeBaseCache::cacheDeleteByClass(static::class, $condition);

        return parent::updateAllCounters($counters, $condition, $params);
    }
}
