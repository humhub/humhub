<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Exception;
use humhub\components\ActiveRecord;
use humhub\helpers\DataTypeHelper;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecordInterface;
use yii\db\IntegrityException;

/**
 * PolymorphicRelations behavior provides simple support for polymorphic relations in ActiveRecords.
 *
 * @since 0.5
 *
 * @property ActiveRecord|object|null $polymorphicRelation
 */
class PolymorphicRelation extends Behavior
{
    /**
     * @var string the class name attribute
     */
    public string $classAttribute = 'object_model';

    /**
     * @var string the primary key attribute
     */
    public string $pkAttribute = 'object_id';

    /**
     * @var bool if set to true, an exception is thrown if `object_model` and `object_id` is set but does not exist
     */
    public bool $strict = false;

    /**
     * @var array the related object needs to be an "instanceof" at least one of these given classnames
     */
    public array $mustBeInstanceOf = [];

    /**
     * @var ActiveRecord|object|null the cached object
     */
    private ?object $cached = null;

    /**
     * Returns the Underlying Object
     *
     * @return ActiveRecordInterface|ActiveRecord|object|null
     * @throws IntegrityException
     */
    public function getPolymorphicRelation(): ?object
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $className = $this->owner->getAttribute($this->classAttribute);
        $primaryKey = $this->owner->getAttribute($this->pkAttribute);

        $record = static::loadActiveRecord($className, $primaryKey);

        if ($this->strict && !$record && !empty($this->classAttribute) && !empty($this->pkAttribute)) {
            throw new IntegrityException(
                'Call to an inconsistent polymorphic relation detected on '
                . ($this->owner === null ? 'NULL' : get_class($this->owner))
                . ' (' . $className . ':' . $primaryKey . ')',
            );
        }

        if ($record !== null && $this->validateUnderlyingObjectType($record)) {
            $this->cached = $record;

            return $record;
        }

        return null;
    }

    /**
     * Sets the related object
     *
     * @param object|null $object
     */
    public function setPolymorphicRelation(?object $object)
    {
        if ($this->cached === $object) {
            return;
        }

        if ($this->validateUnderlyingObjectType($object)) {
            $cached = $this->cached;
            $this->cached = $object;

            if ($object instanceof ActiveRecordInterface) {
                $class = self::getObjectModel($object);
                if ($cached === null || self::getObjectModel($cached) !== $class) {
                    $this->owner->setAttribute($this->classAttribute, $class);
                }

                $pk = $object->getPrimaryKey();
                if ($cached === null || $cached->getPrimaryKey() !== $pk) {
                    $this->owner->setAttribute($this->pkAttribute, $pk);
                }
            }
        }
    }

    public static function getObjectModel(ActiveRecordInterface $object): string
    {
        return $object instanceof ActiveRecord
            ? $object::getObjectModel()
            : get_class($object);
    }

    /**
     * Resets the already loaded $_cached instance of the related object
     */
    public function resetPolymorphicRelation()
    {
        $this->cached = null;
    }

    /**
     * Returns if the polymorphic relation is established
     *
     * @since 1.16
     * @noinspection PhpUnused
     */
    public function isPolymorphicRelationLoaded(): bool
    {
        return $this->cached !== null;
    }

    /**
     * Validates if given object is of an allowed type
     *
     * @param mixed $object
     *
     * @return bool
     */
    private function validateUnderlyingObjectType(?object $object)
    {
        if (empty($this->mustBeInstanceOf)) {
            return true;
        }

        if (DataTypeHelper::matchClassType($object, $this->mustBeInstanceOf)) { //|| $object->asa($instance) !== null
            return true;
        }

        Yii::error('Got invalid underlying object type! (' . get_class($object) . ')');

        return false;
    }


    /**
     * Loads an active record based on classname and primary key.
     *
     * @param string|null $className
     * @param string|int $primaryKey
     *
     * @return null|ActiveRecord|ActiveRecordInterface
     */
    public static function loadActiveRecord(?string $className, $primaryKey): ?ActiveRecordInterface
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

        if (!$class->implementsInterface(ActiveRecordInterface::class)) {
            Yii::error('Could not load polymorphic relation! Class (Class does not implement ActiveRecordInterface: ' . $className . ')');
            return null;
        }

        try {
            /** @var ActiveRecordInterface $className */
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
