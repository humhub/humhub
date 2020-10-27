<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\modules\space\models\Space;

/**
 * This is the model class for table "group_spaces".
 *
 * @property int $id
 * @property int $space_id
 * @property int $group_id
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Group $group
 * @property Space $space
 */
class GroupSpaces extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_spaces';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['space_id', 'group_id'], 'required'],
            [['space_id', 'group_id'], 'integer'],
            [['space_id', 'group_id'], 'unique', 'targetAttribute' => ['space_id', 'group_id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::class, 'targetAttribute' => ['space_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'space_id' => 'Space ID',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if ($this->group !== null && $this->space !== null) {
                foreach ($this->group->groupUsers as $user) {
                    /**@var GroupUser $user**/
                    $this->space->addMember($user->user_id);
                }
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[Space]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }
}
