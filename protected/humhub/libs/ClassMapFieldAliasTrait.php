<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Closure;
use humhub\models\ClassMap;
use Throwable;
use Yii;
use yii\validators\InlineValidator;

/**
 * This trait allows the importing class to treat a set of class_id fields in a way that a corresponding field returns
 * the class_name rather than the id.
 *
 * @author Martin Rüegg
 */
trait ClassMapFieldAliasTrait
{
    use ClassMapBaseTrait;

    public function __get($name)
    {
        if ($key = $this->isClassMappedValue($name)) {
            return $this->getClassMappedValueOf($name, '__get', false, $key);
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($key = $this->isClassMappedValue($name)) {
            return $this->setClassMappedValueFor($name, $value, '__set', false, $key);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::__set($name, $value);
    }

    public function getAttribute($name)
    {
        if ($key = $this->isClassMappedValue($name)) {
            return $this->getClassMappedValueOf($name, 'getAttribute', true, $key);
        }

        return parent::__get($name);
    }

    public function setAttribute($name, $value)
    {
        if ($key = $this->isClassMappedValue($name)) {
            return $this->setClassMappedValueFor($name, $value, 'setAttribute', true, $key);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::setAttribute($name, $value);
    }

    public function setOldAttribute($name, $value)
    {
        if ($key = $this->isClassMappedValue($name)) {
            return $this->setClassMappedValueFor($name, $value, 'setOldAttribute', true, $key);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::setOldAttribute($name, $value);
    }

    abstract public function __isset($name);

    /**
     * @param $targetFieldName
     *
     * @return \Closure
     * @noinspection StaticClosureCanBeUsedInspection
     */
    protected function getClassMapValidator($targetFieldName): Closure
    {
        $model = $this;

        /**
         * @param string $attribute the attribute currently being validated
         * @param mixed $params the value of the "params" given in the rule
         * @param InlineValidator $validator related InlineValidator instance.
         * @param mixed $current the currently validated value of attribute.
         *
         * @see https://www.yiiframework.com/doc/guide/2.0/en/input-validation#inline-validators
         */
        return function (
            string $attribute,
            $params,
            InlineValidator $validator,
            $current
        ) use (
            $model,
            $targetFieldName
        ) {
            try {
                if (
                    is_int($int = $current)
                    || ($int = filter_var($current, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE))
                ) {
                    if (ClassMap::getClassById($int) === null) {
                        $model->addError(
                            Yii::t(
                                'ToDo',
                                'The id {id} does not match any known class in table class_map.',
                                ['id' => $int]
                            )
                        );
                    }
                    $model->$targetFieldName = $int;

                    return;
                }

                $model->$targetFieldName = ClassMap::getIdByName($current);
            } catch (Throwable $t) {
                Yii::warning($t->getMessage());
                $model->addError(Yii::t('ToDo', 'The class {class_name} does exist.', ['class_name' => $$current]));
            }
        };
    }

    /**
     * @param null $fieldName
     *
     * @return string
     */
    protected function getObjectModelOf($fieldName = null): string
    {
        if ($fieldName === null) {
            $fieldName = array_reduce(
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4),
                static function (string $carry, array $item): string {
                    if ($carry !== '' || static::class !== ($item['class'] ?? '')) {
                        return $carry;
                    }
                    return $item['function'];
                },
                ''
            );

            if (strpos($fieldName, 'get') === 0) {
                $fieldName = substr($fieldName, 2);
            }
        }

        return $this->getClassMappedValueOf($fieldName, '__get');
    }
}
