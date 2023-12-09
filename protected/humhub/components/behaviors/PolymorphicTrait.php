<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Exception;
use humhub\libs\Helpers;
use humhub\models\ClassMap;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\IntegrityException;

/**
 * PolymorphicRelations made fast.
 *
 * @since 1.16
 *
 * @property ActiveRecord|object|null $polymorphicRelation
 */
trait PolymorphicTrait
{
    /**
     * Returns the Underlying Object
     *
     * @return ActiveRecord|object|null
     * @throws IntegrityException
     */
    public function getPolymorphicRelation()
    {
        if ($this->polymorphicRecord !== null) {
            return $this->polymorphicRecord;
        }

        $identifier = $this->getPolymorphicIdentifier();
        $settings = $this->getPolymorphicSettings();

        $object = static::loadActiveRecord(...$identifier);

        if ($settings->strict && !$object && !empty($settings->classAttribute) && !empty($settings->pkAttribute)) {
            throw new IntegrityException('Call to an inconsistent polymorphic relation detected on ' . get_class($this->getOwner()) . ' (' . $identifier[0] . ':' . $identifier[1] . ')');
        }

        if ($object !== null && $this->validateUnderlyingObjectType($object)) {
            $this->polymorphicRecord = $object;

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
        if ($this->polymorphicRecord === $object) {
            return;
        }

        $settings = $this->getPolymorphicSettings();

        if ($this->validateUnderlyingObjectType($object)) {
            $cached = $this->polymorphicRecord;
            $this->polymorphicRecord = $object;

            if ($object instanceof ActiveRecord) {
                $owner = $this->getOwner();

                $class = get_class($object);
                if ($cached === null || get_class($cached) !== $class) {
                    $owner->{$settings->classAttribute} = $class;
                }

                $pk = $object->getPrimaryKey();
                if ($cached === null || $cached->getPrimaryKey() !== $pk) {
                    $owner->{$settings->pkAttribute} = $pk;
                }
            }
        }
    }

    /**
     * Resets the already loaded $_cached instance of the related object
     */
    public function resetPolymorphicRelation()
    {
        $this->polymorphicRecord = null;
    }

    /**
     * Validates if given object is of an allowed type
     *
     * @param mixed $object
     *
     * @return boolean
     */
    private function validateUnderlyingObjectType($object): bool
    {
        if (empty($this->mustBeInstanceOf)) {
            return true;
        }

        if (Helpers::checkClassType($object, $this->mustBeInstanceOf, false)) { //|| $object->asa($instance) !== null
            return true;
        }

        Yii::error('Got invalid underlying object type! (' . get_class($object) . ')');

        return false;
    }


    /**
     * Loads an active record based on classname and primary key.
     *
     * @param $className
     * @param $primaryKey
     *
     * @return null|ActiveRecord
     */
    public static function loadActiveRecord($className, $primaryKey)
    {
        if (empty($className) || empty($primaryKey)) {
            return null;
        }

        // check if $className is an integer and valid classId
        if (null !== $classId = filter_var($className, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)) {
            $className = ClassMap::getClassById($classId);
            if ($className === null) {
                Yii::error('Could not load polymorphic relation! (Invalid ClassId: ' . $className . ')');
                return null;
            }
        }

        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            Yii::error('Could not load polymorphic relation! Class: ' . $className . ' (' . $e->getMessage() . ')');
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

    /**
     * @return ActiveRecord
     */
    protected function getOwner(): ActiveRecord
    {
        return $this instanceof PolymorphicRelation ? $this->owner : $this;
    }

    /**
     *
     * @return array
     */
    protected function getPolymorphicIdentifier(): array
    {
        $owner = $this->getOwner();
        $settings = $this->getPolymorphicSettings();

        return [
            $owner->getAttribute($settings->classAttribute),
            $owner->getAttribute($settings->pkAttribute)
        ];
    }

    /**
     * @return object = (object) [
     *     'classAttribute' => string,
     *     'pkAttribute' => string,
     *     'strict' => bool,
     *     'mustBeInstanceOf' => string[]
     * ]
     */
    protected function getPolymorphicSettings(): object
    {
        return (object)[
            'classAttribute' => $this->classAttribute,
            'pkAttribute' => $this->pkAttribute,
            'strict' => $this->strict,
            'mustBeInstanceOf' => $this->mustBeInstanceOf,
        ];
    }
}
