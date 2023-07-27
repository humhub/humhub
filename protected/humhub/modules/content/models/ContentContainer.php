<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\file\models\AttachedImageIntermediateInterface;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use yii\base\InvalidCallException;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contentcontainer".
 *
 * @property integer $id
 * @property string $guid
 * @property string $class
 * @property integer $pk
 * @property integer $owner_user_id
 * @property string $tags_cached readonly, a comma separted list of assigned tags
 * @mixin PolymorphicRelation
 */
class ContentContainer extends ActiveRecord implements AttachedImageIntermediateInterface
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
            [['class', 'pk', 'guid'], 'required'],
            [['guid', 'class'], 'string', 'max' => 255],
            [['class', 'pk'], 'unique', 'targetAttribute' => ['class', 'pk'], 'message' => 'The combination of Class and Pk has already been taken.'],
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
                'pkAttribute' => 'pk'
            ]
        ];
    }

    /**
     * @param $guid
     * @return ContentContainerActiveRecord|null
     * @throws \yii\db\IntegrityException
     * @since 1.4
     */
    public static function findRecord($guid)
    {
        $instance = static::findOne(['guid' => $guid]);
        return $instance ? $instance->getPolymorphicRelation() : null;
    }

    public function findImageOwner(): ?AttachedImageOwnerInterface
    {
        return static::findRecord($this->guid);
    }

    public static function getImageOwnerClass(): string
    {
        throw new InvalidCallException(sprintf('Method %s must be implemented by subclass', __METHOD__));
    }
}
