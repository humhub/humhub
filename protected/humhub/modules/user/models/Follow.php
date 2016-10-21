<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;


use humhub\modules\activity\models\Activity;

/**
 * This is the model class for table "user_follow".
 *
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $send_notifications
 */
class Follow extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_follow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_model', 'object_id', 'user_id'], 'required'],
            [['object_id', 'user_id', 'send_notifications'], 'integer'],
            [['object_model'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_model' => 'Object Model',
            'object_id' => 'Object ID',
            'user_id' => 'User ID',
            'send_notifications' => 'Send Notifications',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {

        if ($insert && $this->object_model == User::className()) {
            $notification = new \humhub\modules\user\notifications\Followed();
            $notification->originator = $this->user;
            $notification->send($this->getTarget());

            $activity = new \humhub\modules\user\activities\UserFollow();
            $activity->source = $this;
            $activity->container = $this->user;
            $activity->originator = $this->user;
            $activity->create();
        }



        return parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {

        // ToDo: Handle this via event of User Module
        if ($this->object_model == User::className()) {
            $notification = new \humhub\modules\user\notifications\Followed();
            $notification->originator = $this->user;
            $notification->delete($this->getTarget());

            foreach (Activity::findAll(['object_model' => $this->className(), 'object_id' => $this->id]) as $activity) {
                $activity->delete();
            }
        }

        return parent::beforeDelete();
    }

    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    public function getTarget()
    {
        $targetClass = $this->object_model;
        if ($targetClass != "") {
            return $targetClass::findOne(['id' => $this->object_id]);
        }
        return null;
    }

}
