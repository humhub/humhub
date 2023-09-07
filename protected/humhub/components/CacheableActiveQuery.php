<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * ActiveQueryContent is an enhanced ActiveQuery with additional selectors for especially content.
 *
 * @inheritdoc
 */
class CacheableActiveQuery extends ActiveQuery
{
    public function findFor($name, $model)
    {
        $result = parent::findFor($name, $model);

        if (!$result instanceof ActiveRecord) {
            return $result;
        }

        return static::cacheSetVariants($result);
    }

    public static function normaliseObjectIdentifier($classOrObject, $id = null, ?string &$idWasUsed = null): string
    {
        if (is_object($classOrObject)) {
            $classOrObject = get_class($classOrObject);
        } elseif (!is_string($classOrObject)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$classOrObject'],
                ['object', 'string'],
                $classOrObject
            );
        }

        $idWasUsed = '';
        $id = $id === 0 || !empty($id) ? "__" . ($idWasUsed = implode('_', (array)$id)) : '';

        return str_replace('\\', '_', ltrim($classOrObject, '\\')) . $id;
    }

    /**
     * @param string $action
     * @param object|ActiveRecord|null $record
     * @param array|null $properties
     * @param array|null $done
     *
     * @return void
     */
    protected static function cacheProcessVariants(string $action, ?object $record, ?array $properties = ['id', 'guid'], ?array &$done = null): ?object
    {
        if ($record === null) {
            return null;
        }

        $runtimeCache = Yii::$app->runtimeCache;
        $properties ??= ['id', 'guid'];
        $done ??= [];
        $class = get_class($record);

        if ($record instanceof BaseActiveRecord) {
            $identifier = self::normaliseObjectIdentifier($class, $record->getPrimaryKey(true));

            if (!in_array($identifier, $done, true)) {
                $runtimeCache->$action($identifier, $record);
            }

            $done[] = $identifier;
        }

        foreach ($properties as $identifier) {
            if (property_exists($record, $identifier) || ($record instanceof BaseActiveRecord && $record->hasAttribute($identifier))) {
                $identifier = self::normaliseObjectIdentifier($class, (string)$record->$identifier);
                if (!in_array($identifier, $done, true)) {
                    $runtimeCache->$action($identifier, $record);
                    $done[] = $identifier;
                }
            }
        }

        return $record;
    }

    /**
     * @param object|ActiveRecord|null $record
     * @param array|null $properties
     * @param array|null $done
     *
     * @return void
     */
    public static function cacheDeleteVariants(?object $record, ?array $properties = ['id', 'guid'], ?array &$done = null): ?object
    {
        $runtimeCache = Yii::$app->runtimeCache;

        $record = static::cacheProcessVariants('delete', $record, $properties, $done);

        if ($record instanceof ContentActiveRecord) {
            $identifier = self::normaliseObjectIdentifier(Content::class, [get_Class($record), ...array_values($record->getPrimaryKey(true))]);
            if (!in_array($identifier, $done, true)) {
                $runtimeCache->delete($identifier);
                $done[] = $identifier;
            }

            if ($record->isRelationPopulated('content') && !$record->content->getIsNewRecord()) {
                $identifier = self::normaliseObjectIdentifier(Content::class, $record->content->id);
                if (!in_array($identifier, $done, true)) {
                    $runtimeCache->delete($identifier);
                    $done[] = $identifier;
                }
            }
        }

        /**
         * Check if we have the related record cached in the polymorphic behavior, so we can delete the cache by ID.
         * (This is not fully bullet-proof, as the object might still be saved in the cache, but only under the guid key.)
         */
        if (!$record instanceof Component || !$record->hasMethod('getPolymorphicRelation')) {
            return $record;
        }

        $identifier = self::normaliseObjectIdentifier($record->{$record->classAttribute}, $record->{$record->pkAttribute});

        if (!in_array($identifier, $done, true) && $model = $record->getPolymorphicRelation(false) ?? $runtimeCache->get($identifier)) {
            static::cacheDeleteVariants($model, $properties, $done);
        }

        return $record;
    }

    /**
     * @param object|ActiveRecord|null $record
     * @param array|null $properties
     * @param array|null $done
     *
     * @return void
     */
    public static function cacheSetVariants(?object $record, ?array $properties = ['id', 'guid'], ?array &$done = null): ?object
    {
        return static::cacheProcessVariants('set', $record, $properties, $done);
    }

    public static function cacheDeleteByClass($class, $condition)
    {
        $cache = Yii::$app->runtimeCache;

        if (static::isSimpleCondition($condition) && $class::isPrimaryKey(array_keys($condition))) {
            $key = self::normaliseObjectIdentifier($class, $condition);

            if ($record = $cache->get($key)) {
                self::cacheDeleteVariants($record);
                $cache->delete($key);
            }
        } else {
            $cache->flush();
        }
    }

    public static function isSimpleCondition(&$condition, bool $allowNull = false, bool $allowIterable = false): bool
    {
        if ($allowNull && $condition === null) {
            return true;
        }

        if (!is_array($condition) && (!$allowIterable || !is_iterable($condition))) {
            return false;
        }

        $count = 0;

        foreach ($condition as $key => $value) {
            if (!is_string($key)) {
                return false;
            }
            if (!is_scalar($value)) {
                return false;
            }
            $count++;
        }

        if ($allowNull && $count === 0) {
            $condition = null;
        }

        return true;
    }
}
