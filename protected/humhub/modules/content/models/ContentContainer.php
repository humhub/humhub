<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\libs\UUIDValidator;
use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\db\IntegrityException;

/**
 * This is the model class for table "contentcontainer".
 *
 * @property int $id
 * @property string $guid
 * @property string $class
 * @property int $pk
 * @property int $owner_user_id
 * @property string $tags_cached readonly, a comma separted list of assigned tags
 * @mixin PolymorphicRelation
 * @noinspection PropertiesInspection
 */
class ContentContainer extends ActiveRecord
{
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
            [['pk', 'owner_user_id'], 'integer'],
            [['class', 'pk'], 'required'],
            [['class'], 'string', 'max' => 255],
            [['class', 'pk'], 'unique', 'targetAttribute' => ['class', 'pk'], 'message' => 'The combination of Class and Pk has already been taken.'],
            [['guid'],
                UUIDValidator::class,
                'autofillWith' => false,
                'allowNull' => false,
                'messageOnForbiddenNull' => 'Cannot not create standalone ContentContainer instance. Instance will be automatically created on ContentContainerActiveRecord::afterSave()',
            ],
            [['guid'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'class' => 'Class',
            'pk' => 'Pk',
            'owner_user_id' => 'Owner User ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [ContentContainerActiveRecord::class],
                'classAttribute' => 'class',
                'pkAttribute' => 'pk',
            ],
        ];
    }

    /**
     * @param $guid
     * @return ContentContainerActiveRecord|null
     * @throws IntegrityException
     * @since 1.4
     */
    public static function findRecord($guid)
    {
        $instance = static::findOne(['guid' => $guid]);
        return $instance ? $instance->getPolymorphicRelation() : null;
    }
}
