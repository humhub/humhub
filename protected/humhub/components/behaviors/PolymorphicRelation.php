<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Exception;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * PolymorphicRelations behavior provides simple support for polymorphic relations in ActiveRecords.
 *
 * @since 0.5
 */
class PolymorphicRelation extends Behavior
{

    /**
     * @var string the class name attribute
     */
    public $classAttribute = 'object_model';

    /**
     * @var string the primary key attribute
     */
    public $pkAttribute = 'object_id';

    /**
     * @var array the related object needs to be a "instanceof" at least one of these given classnames
     */
    public $mustBeInstanceOf = [];

    /**
     * @var mixed the cached object
     */
    private $_cached = null;

    /**
     * Returns the Underlying Object
     *
     * @return mixed
     */
    public function getPolymorphicRelation()
    {
        if ($this->_cached !== null) {
            return $this->_cached;
        }

        $object = static::loadActiveRecord(
            $this->owner->getAttribute($this->classAttribute),
            $this->owner->getAttribute($this->pkAttribute)
        );

        if ($object !== null && $this->validateUnderlyingObjectType($object)) {
            $this->_cached = $object;
            return $object;
        }

        return null;
    }

    /**
     * Sets the related object
     *
     * @param mixed $object
     */
    public function setPolymorphicRelation($object)
    {
        if ($this->validateUnderlyingObjectType($object)) {
            $this->_cached = $object;
            if ($object instanceof \yii\db\ActiveRecord) {
                $this->owner->setAttribute($this->classAttribute, $object->className());
                $this->owner->setAttribute($this->pkAttribute, $object->getPrimaryKey());
            }
        }
    }

    /**
     * Resets the already loaded $_cached instance of related object
     */
    public function resetPolymorphicRelation()
    {
        $this->_cached = null;
    }

    /**
     * Validates if given object is of allowed type
     *
     * @param mixed $object
     * @return boolean
     */
    private function validateUnderlyingObjectType($object)
    {
        if (count($this->mustBeInstanceOf) == 0) {
            return true;
        }

        foreach ($this->mustBeInstanceOf as $instance) {
            if ($object instanceof $instance) { //|| $object->asa($instance) !== null
                return true;
            }
        }

        Yii::error('Got invalid underlying object type! (' . $object->className() . ')');

        return false;
    }


    /**
     * Loads an active record based on classname and primary key.
     *
     * @param $className
     * @param $primaryKey
     * @return null|ActiveRecord
     */
    public static function loadActiveRecord($className, $primaryKey)
    {
        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            Yii::error('Could not load polymorphic relation! Class (' . $e->getMessage() . ')');
            return null;
        }

        if (!$class->isSubclassOf(BaseActiveRecord::class)) {
            Yii::error('Could not load polymorphic relation! Class (Class is no ActiveRecord: ' . $className . ')');
            return null;
        }

        try {
            $primaryKeyNames = $className::primaryKey();
            if (count($primaryKeyNames) !== 1) {
                Yii::error('Could not load polymorphic relation! Only one primary key is supported!');
                return null;
            }

            return $className::findOne([$primaryKeyNames[0] => $primaryKey]);
        } catch (Exception $ex) {
            Yii::error('Could not load polymorphic relation! Error: "' . $ex->getMessage());
        }

        return null;
    }
}
