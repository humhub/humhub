<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;

/**
 * This is the model class for table "group_admin".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $group_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class GroupUser extends ActiveRecord
{

    const SCENARIO_REGISTRATION = 'registration';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id'], 'required'],
            [['user_id', 'group_id'], 'integer'],
            [['group_id'], 'validateGroupId'],
            [['user_id', 'group_id'], 'unique', 'targetAttribute' => ['user_id', 'group_id'], 'message' => 'The combination of User ID and Group ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTRATION] = ['group_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'group_id' => 'Group ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if ($this->group !== null && $this->group->space !== null) {
                $this->group->space->addMember($this->user->id);
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Validator for group field during registration
     */
    public function validateGroupId()
    {
        if ($this->scenario == static::SCENARIO_REGISTRATION) {
            if ($this->group_id != '') {
                $registrationGroups = Group::getRegistrationGroups();
                foreach ($registrationGroups as $group) {
                    if ($this->group_id == $group->id) {
                        return;
                    }
                }

                // Not found group in groups available during registration
                $this->addError('group_id', 'Invalid group given!');
            }
        }
    }

}
