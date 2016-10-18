<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;


use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * This is the model class for table "contentcontainer".
 *
 * @property integer $id
 * @property string $guid
 * @property string $class
 * @property integer $pk
 * @property integer $owner_user_id
 * @property integer $wall_id
 */
class ContentContainer extends \yii\db\ActiveRecord
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
            [['pk', 'owner_user_id', 'wall_id'], 'integer'],
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
            'wall_id' => 'Wall ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::className(),
                'mustBeInstanceOf' => array(ContentContainerActiveRecord::className()),
                'classAttribute' => 'class',
                'pkAttribute' => 'pk'
            ]
        ];
    }

}
