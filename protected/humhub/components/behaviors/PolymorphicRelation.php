<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use yii\base\Behavior;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * PolymorphicRelations behavior provides simple support for polymorphic relations in ActiveRecords.
 *
 * @since 0.5
 *
 * @property ActiveRecord|object|null $polymorphicRelation
 */
class PolymorphicRelation extends Behavior
{
    use PolymorphicTrait;

    /**
     * @var string the class name attribute
     */
    public string $classAttribute = 'object_model';

    /**
     * @var string the primary key attribute
     */
    public string $pkAttribute = 'object_id';

    /**
     * @var boolean if set to true, an exception is thrown if `object_model` and `object_id` is set but does not exist
     */
    public bool $strict = false;

    /**
     * @var array the related object needs to be an "instanceof" at least one of these given classnames
     */
    public array $mustBeInstanceOf = [];

    /**
     * @var ActiveRecord|object|null the cached object
     */
    protected $polymorphicRecord = null;

    public static function getObjectModel(Model $object): string
    {
        return $object instanceof ContentActiveRecord || $object instanceof ContentAddonActiveRecord
            ? $object::getObjectModel()
            : get_class($object);
    }
}
