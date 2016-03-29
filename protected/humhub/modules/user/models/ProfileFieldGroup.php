<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;

/**
 * This is the model class for table "profile_field_assignment".
 *
 * @property integer $group_id
 * @property integer $profile_field_id
 */
class ProfileFieldGroup extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile_field_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['profile_field_id', 'group_id'], 'required']
            ];
            
    }
    
     public function scenarios()
     {
         $scenarios = parent::scenarios();
         $scenarios['fieldAssignment'] = ['profile_field_id', 'group_id'];
         return $scenarios;
     }

  /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'profile_field_id' => array(self::BELONGS_TO, 'ProfileField', 'profile_field_id'),
            'group_id' => array(self::BELONGS_TO, 'field_assignment_group', 'field_assignment_group_id'),
        );
    }
}