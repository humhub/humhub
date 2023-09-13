<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\helpers;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\UniqueIdentifiersInterface;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use ReflectionObject;
use Yii;
use yii\base\Component;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

class RuntimeCacheHelper
{
    /**
     * @param object|mixed|null $item
     * @param string|string[]|null $properties Optional. List of properties or set of properties that will be used to create additional key variants.
     * @param bool|null $throw If true, an Exception will be thrown if $item is not an object or null.
     * @param bool|null $checkUniqueIdentifiersInterface If true, UniqueIdentifiersInterface::getUniqueIDs() will not be called to prevent infinite recursion.
     *
     * @return string[]|null Key-Hash value pairs, or null for skipped entries
     */
    public static function &buildUniqueIDs($item = null, ?array $properties = null, ?bool $throw = false, ?bool $checkUniqueIdentifiersInterface = true): ?array
    {
        $uniqueIDs = null;

        // if $item is null, return null
        if ($item === null) {
            return $uniqueIDs;
        }

        // if $item is not an object, throw an exception or return null, depending on the $throw flag
        if (!is_object($item)) {
            if ($throw) {
                throw new InvalidArgumentTypeException(
                    __METHOD__,
                    [1 => '$value'],
                    [ActiveRecordInterface::class, UniqueIdentifiersInterface::class, 'object'],
                    $item,
                    true
                );
            }

            return $uniqueIDs;
        }

        // get unique ID's from the $item itself, if it implements UniqueIdentifiersInterface, and it's not a recursive call
        if ($checkUniqueIdentifiersInterface && $item instanceof UniqueIdentifiersInterface) {
            $uniqueIDs = $item->getUniqueIdVariants($properties);
        } else {
            // otherwise, define some default properties to be used

            if ($item instanceof ActiveRecordInterface) {
                // use the properties ID and GUID if none are defined and they exist
                if ($properties === null) {
                    $properties = ['id', 'guid'];
                    $properties = array_intersect($properties, $item->attributes());
                }

                // additionally, create a key with the $item's Primary Key
                $uniqueIDs[] = static::normaliseObjectIdentifier($item, $item->getPrimaryKey(true));
            } elseif ($properties === null) {
                // use the properties ID and GUID if none are defined and they exist
                $c = new ReflectionObject($item);
                $properties = ['id', 'guid'];
                $properties = array_intersect($properties, array_column($c->getProperties(), 'name'));
            }

            if (method_exists($item, 'getUniqueId')) {
                $uniqueIDs[] = $item->getUniqueId();
            }
        }

        if ($properties !== null) {
            foreach ($properties as $i => &$property) {
                // check if the index of the property is a string, in which case the $property is used as-is
                if (is_string($i)) {
                    $uniqueIDs[] = static::normaliseObjectIdentifier($item, $property);

                    continue;
                }

                // if the property is a string, use it as the property name to get the value from $item
                if (is_string($property)) {
                    $uniqueIDs[] = static::normaliseObjectIdentifier($item, $item->$property);

                    continue;
                }

                // make sure the $property is an array
                if (!is_array($property)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => sprintf('$properties[%s]', $i)],
                        ['string[]', 'string'],
                        $property
                    );
                }

                // we use the list of properties as the key for the resulting composite key
                $property = array_flip($property);

                foreach ($property as $key => &$value) {
                    // make sure the $key is a string and can be used as a property name
                    if (!is_string($key)) {
                        throw new InvalidArgumentTypeException(
                            __METHOD__,
                            [1 => sprintf('$properties[%s][%s]', $i, $key)],
                            ['string[]', 'string'],
                            $key
                        );
                    }

                    // store the value in the resulting composite key
                    $value = $item->$key;
                }
                unset($value);

                // now create the unique index using the collected values
                $uniqueIDs[] = static::normaliseObjectIdentifier($item, $property);
            }
        }
        unset($property);

        if ($uniqueIDs === null) {
            return $uniqueIDs;
        }

        // make sure the result is either an array with at least one value (and the values are unique) or null
        $uniqueIDs = count($uniqueIDs) === 0 ? null : array_unique($uniqueIDs);

        return $uniqueIDs;
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
        if ($id === 0 || !empty($id)) {
            $id = "__" . ($idWasUsed = implode('_', array_map('json_encode', (array)$id)));
        } else {
            $id = '';
        }

        return str_replace('\\', '_', ltrim($classOrObject, '\\')) . $id;
    }

    /**
     * @param object|ActiveRecord|null $object
     */
    protected static function processVariants(string $action, ?object $object, ?array $properties = null, ?array &$done = null): ?object
    {
        $done ??= [];

        if ($object === null) {
            return null;
        }

        $runtimeCache = Yii::$app->runtimeCache;

        $identifiers = static::buildUniqueIDs($object, $properties);

        if ($identifiers === null) {
            return $object;
        }

        foreach ($identifiers as $identifier) {
            if (!in_array($identifier, $done, true)) {
                $runtimeCache->$action($identifier, $object);
                $done[] = $identifier;
            }
        }

        return $object;
    }

    public static function set($value, ?string $key = null)
    {
        static::setVariants($value, null, $done);

        if ($key && !in_array($key, $done, true)) {
            Yii::$app->runtimeCache->set($key, $value);
        }

        return $value;
    }

    /**
     * @param object|ActiveRecord|null $record
     */
    public static function setVariants(?object $record, ?array $properties = null, ?array &$done = null): ?object
    {
        return static::processVariants('set', $record, $properties, $done);
    }

    /**
     * @param object|ActiveRecord|null $record
     */
    public static function deleteVariants(?object $record, ?array $properties = null, ?array &$done = null): ?object
    {
        $runtimeCache = Yii::$app->runtimeCache;

        $record = static::processVariants('delete', $record, $properties, $done);

        if ($record instanceof ContentActiveRecord) {
            $identifier = static::normaliseObjectIdentifier(
                Content::class,
                [get_Class($record), ...array_values($record->getPrimaryKey(true))]
            );

            if (!in_array($identifier, $done, true)) {
                $runtimeCache->delete($identifier);
                $done[] = $identifier;
            }

            if ($record->isRelationPopulated('content') && !$record->content->getIsNewRecord()) {
                $identifier = static::normaliseObjectIdentifier(Content::class, $record->content->id);

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

        $identifier = static::normaliseObjectIdentifier(
            $record->{$record->classAttribute},
            $record->{$record->pkAttribute}
        );

        if (!in_array($identifier, $done, true)) {
            $model = $record->getPolymorphicRelation(false) ?? $runtimeCache->get($identifier);

            if ($model) {
                static::deleteVariants($model, $properties, $done);
            }
        }

        return $record;
    }

    public static function deleteByKey(string $key)
    {
        $instance = Yii::$app->runtimeCache->get($key);

        return static::deleteByInstance($instance, $key);
    }

    public static function deleteByInstance(?object $instance, ?string $key = null): ?object
    {
        $runtimeCache = Yii::$app->runtimeCache;

        if ($instance === null && $key !== null) {
            $instance = $runtimeCache->get($key);
        }

        if ($instance) {
            static::deleteVariants($instance, null, $done);
        }

        if ($instance !== false && $key && !in_array($key, $done, true)) {
            $runtimeCache->delete($key);
        }

        return $instance ?: null;
    }

    public static function deleteByClass($classOrObject, $condition = null)
    {
        if ($classOrObject === null) {
            return;
        }

        if (is_object($classOrObject)) {
            if ($condition === null) {
                // try to delete the items in the cache base on the unique identifiers derived from $classOrObject
                static::deleteVariants($classOrObject, null, $done);

                // if the list of unique identifiers (returned in $done) is not empty, this was successful
                if (count($done)) {
                    return;
                }
            }

            // otherwise, use the class name for tag deletion further down
            $classOrObject = get_class($classOrObject);
        } elseif (!is_string($classOrObject)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$classOrObject'],
                ['object', 'string'],
                $classOrObject
            );
        }

        $cache = Yii::$app->runtimeCache;

        // check if the $condition is a simple condition and corresponds with the Primary Key of the model, in which case only that item is deleted.
        if (
            is_a($classOrObject, ActiveRecordInterface::class, true)
            && static::isSimpleCondition($condition)
            && $classOrObject::isPrimaryKey(array_keys($condition))
        ) {
            $key = static::normaliseObjectIdentifier($classOrObject, $condition);

            if ($record = $cache->get($key)) {
                static::deleteVariants($record, null, $done);
                if (!in_array($key, $done, true)) {
                    $cache->delete($key);
                }
            }
        } else {
            // otherwise, we flush the entire cache
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

    public static function flush()
    {
        if (Yii::$app) {
            $runtimeCache = Yii::$app->runtimeCache;
            $runtimeCache->flush();
        }
    }
}
