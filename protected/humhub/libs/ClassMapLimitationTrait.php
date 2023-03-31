<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\models\ClassMap;
use yii\db\ActiveQuery;

/**
 * This trait allows the importing class with a (set of) class_id field(s) to only return those records, whose owning
 * module is active..
 *
 * @author Martin Rüegg
 */
trait ClassMapLimitationTrait
{
    use ClassMapBaseTrait;

    /**
     *
     * @return ActiveQuery
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function find()
    {
        $query = parent::find();

        static::$classMapFields ??= static::classMappedFields();

        $tableCurrent = static::tableName();

        array_walk(
            static::$classMapFields,
            static function ($alias, $column) use ($query, $tableCurrent) {
                ClassMap::joinClassMap(
                    $query,
                    $tableCurrent,
                    $column,
                    'id',
                    null,
                    null,
                    ['class_name' => $alias]
                );
            }
        );

        return $query;
    }

    abstract public static function tableName();
}
