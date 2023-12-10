<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\behaviors\PolymorphicTrait;
use humhub\components\GlobalIdTrait;
use humhub\interfaces\GlobalActiveRecordInterface;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\user\models\User;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "contentcontainer".
 *
 * @property integer $id
 * @property integer $gid
 * @property string $guid
 * @property string $class
 * @property integer $pk
 * @property integer $owner_user_id
 * @property string $tags_cached readonly, a comma-separated list of assigned tags
 * @mixin PolymorphicRelation
 * @noinspection PropertiesInspection
 */
class ContentContainer extends ActiveRecord implements GlobalActiveRecordInterface
{
    use GlobalIdTrait;
    use PolymorphicTrait;

    /**
     * @var ContentContainerActiveRecord|null the cached object
     */
    protected ?ContentContainerActiveRecord $polymorphicRecord = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'pk', 'owner_user_id'], 'integer'],
            [['gid', 'class', 'pk', 'guid'], 'required'],
            [['guid', 'class'], 'string', 'max' => 255],
            [
                ['class', 'pk'],
                'unique',
                'targetAttribute' => ['class', 'pk'],
                'message' => 'The combination of Class and Pk has already been taken.'
            ],
            [['guid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gid' => 'Global ID',
            'guid' => 'Guid',
            'class' => 'Class',
            'pk' => 'Pk',
            'owner_user_id' => 'Owner User ID',
        ];
    }

    /**
     * @param ContentContainerActiveRecord|null $object
     *
     * @return void
     */
    public function setPolymorphicRelation($object)
    {
        if ($this->polymorphicRecord === $object) {
            return;
        }

        Helpers::checkClassType($object, [null, ContentContainerActiveRecord::class]);

        $this->polymorphicRecord = $object;

        if ($object === null || !$this->getIsNewRecord()) {
            return;
        }

        $this->gid = $object->gid;
        $this->guid = $object->guid;
        $this->class = get_class($object);
        $this->pk = $object->getPrimaryKey();
        if ($object instanceof User) {
            $this->owner_user_id = $object->id;
        } elseif ($object->hasAttribute('created_by')) {
            $this->owner_user_id = $object->created_by;
        }

        $this->save();
    }

    protected function getPolymorphicIdentifier(): array
    {
        return [
            $this->globalIdRecord->getClass(),
            $this->gid
        ];
    }

    protected function getPolymorphicSettings(): object
    {
        return (object)[
            'mustBeInstanceOf' => [ContentContainerActiveRecord::class],
            'classAttribute' => null,
            'pkAttribute' => 'gid',
            'strict' => false,
        ];
    }

    /**
     * @param $guid
     *
     * @return ContentContainerActiveRecord|null
     * @throws \yii\db\IntegrityException
     * @since 1.4
     */
    public static function findRecord($guid)
    {
        $instance = static::findOne(['guid' => $guid]);
        return $instance ? $instance->getPolymorphicRelation() : null;
    }

    public static function moduleId(): string
    {
        return 'content';
    }
}
