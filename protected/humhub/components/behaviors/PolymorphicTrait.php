<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Exception;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Model;
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
class PolymorphicTrait
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
     * @var boolean if set to true, an exception is thrown if `object_model` and `object_id` is set but does not exist
     */
    public $strict = false;

    /**
     * @var array the related object needs to be an "instanceof" at least one of these given classnames
     */
    public $mustBeInstanceOf = [];

    /**
     * @var ActiveRecord|object|null the cached object
     */
    protected $polymorphRecord = null;

    /**
     * Returns the Underlying Object
     *
     * @return ActiveRecord|object|null
     * @throws IntegrityException
     */
    public function getPolymorphicRelation()
    {
        if ($this->polymorphRecord !== null) {
            return $this->polymorphRecord;
        }

        $owner = $this->getOwner();

        $object = static::loadActiveRecord(
            $owner->getAttribute($this->classAttribute),
            $owner->getAttribute($this->pkAttribute)
        );

        if ($this->strict && !$object && !empty($this->classAttribute) && !empty($this->pkAttribute)) {
            throw new IntegrityException('Call to an inconsistent polymorphic relation detected on ' . get_class($owner) . ' (' . $owner->getAttribute($this->classAttribute) . ':' . $owner->getAttribute($this->pkAttribute) . ')');
        }

        if ($object !== null && $this->validateUnderlyingObjectType($object)) {
            $this->polymorphRecord = $object;

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
        if ($this->polymorphRecord === $object) {
            return;
        }

        if ($this->validateUnderlyingObjectType($object)) {
            $cached = $this->polymorphRecord;
            $this->polymorphRecord = $object;

            if ($object instanceof ActiveRecord) {
                $owner = $this->getOwner();

                $class = get_class($object);
                if ($cached === null || get_class($cached) !== $class) {
                    $owner->setAttribute($this->classAttribute, $class);
                }

                $pk = $object->getPrimaryKey();
                if ($cached === null || $cached->getPrimaryKey() !== $pk) {
                    $owner->setAttribute($this->pkAttribute, $pk);
                }
            }
        }
    }

    /**
     * Resets the already loaded $_cached instance of the related object
     */
    public function resetPolymorphicRelation()
    {
        $this->polymorphRecord = null;
    }

    /**
     * Validates if given object is of an allowed type
     *
     * @param mixed $object
     *
     * @return boolean
     */
    private function validateUnderlyingObjectType($object)
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this instanceof PolymorphicRelation ? $this->owner : $this;
    }
}
