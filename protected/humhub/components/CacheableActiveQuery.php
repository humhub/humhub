<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\exceptions\InvalidArgumentTypeException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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

        static::cacheProcessVariants('set', $result);

        return $result;
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
     * @param ActiveRecord $record
     * @param array $properties
     *
     * @return void
     */
    public static function cacheProcessVariants(string $action, ActiveRecord $record, array $properties = ['id', 'guid']): void
    {
        static $runtimeCache;

        $runtimeCache ??= Yii::$app->runtimeCache;

        $done = [];
        $class = get_class($record);

        $pk = $record->getPrimaryKey(true);

        $runtimeCache->$action(self::normaliseObjectIdentifier($class, $pk, $identifier), $record);

        $done[] = $identifier;

        foreach ($properties as $identifier) {
            if ($record->hasAttribute($identifier)) {
                $identifier = (string)$record->$identifier;
                if (!in_array($identifier, $done, true)) {
                    $runtimeCache->$action(self::normaliseObjectIdentifier($class, $identifier, $identifier), $record);
                    $done[] = $identifier;
                }
            }
        }

        /**
         * Check if we have the related record cached in the polymorphic behavior, so we can delete the cache by ID.
         * (This is not fully bullet-proof, as the object might still be saved in the cache, but only under the guid key.)
         */
        if ($action !== 'delete' || !$record->hasMethod('getPolymorphicRelation')) {
            return;
        }

        if ($model = $record->getPolymorphicRelation(false) ?? $runtimeCache->get(self::normaliseObjectIdentifier($record->{$record->classAttribute}, $record->{$record->pkAttribute}))) {
            static::cacheProcessVariants($action, $model, $properties);
        }
    }

    public static function cacheDeleteByClass($class, $condition)
    {
        $cache = Yii::$app->runtimeCache;

        if (is_array($condition) && $class::isPrimaryKey(array_keys($condition))) {
            $key = self::normaliseObjectIdentifier($class, $condition);

            if ($record = $cache->get($key)) {
                self::cacheProcessVariants('delete', $record);
                $cache->delete($key);
            }
        } else {
            $cache->flush();
        }
    }
}
