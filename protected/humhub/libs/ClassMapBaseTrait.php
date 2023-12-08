<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\models\ClassMap;
use ReflectionException;
use yii\base\ErrorException;

/**
 * This trait allows the importing class to treat a set of class_id fields in a way that a corresponding field returns
 * the class_name rather than the id
 *
 * @author Martin Rüegg
 */
trait ClassMapBaseTrait
{
    // protected properties
    protected static ?array $classMapFields = null;


    protected function getClassMappedValueOf(string $name, string $parentMethod = '', bool $getSourceValueFormParent = true, ?string $key = null)
    {
        $key ??= func_num_args() < 4 ? $this->isClassMappedValue($name) : null;

        if ($key === null) {
            return $parentMethod
                ? parent::$parentMethod($name)
                : null;
        }

        if ($getSourceValueFormParent) {
            $value = $parentMethod
                ? parent::$parentMethod($key)
                : null;
        } else {
            $value = $this->$key;
        }

        return $value !== null
            ? ClassMap::getClassById($value)
            : null;
    }


    /**
     * @throws ReflectionException
     * @throws ErrorException
     */
    protected function setClassMappedValueFor(string $name, $value, string $parentMethod, bool $setValueThroughParent = false, ?string $key = null)
    {
        $key ??= func_num_args() < 4 ? $this->isClassMappedValue($name) : null;

        if ($key === null) {
            return parent::$parentMethod($name, $value);
        }

        $value = is_array($value)
            ? ClassMap::getIdByManyNames($value)
            : ClassMap::getIdByName($value);

        return $setValueThroughParent
            ? parent::$parentMethod($key, $value)
            : $this->$key = $value;
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function isClassMappedValue(string $name): ?string
    {
        static::$classMapFields ??= static::classMappedFields();

        $key = array_search($name, static::$classMapFields, true);

        return $key ?: null;
    }

    /**
     * @return array consisting of "id_field" => "class_name_field" pairs.
     *                  E.g: ['class_id' => 'class_name']
     */
    abstract protected static function classMappedFields(): array;
}
