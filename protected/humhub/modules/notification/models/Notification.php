<?php

namespace humhub\modules\notification\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property integer $id
 * @property string $class
 * @property integer $user_id
 * @property integer $seen
 * @property string $source_class
 * @property integer $source_pk
 * @property integer $space_id
 * @property integer $emailed
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $desktop_notified
 * @property integer $originator_user_id
 */
class Notification extends \humhub\components\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'user_id'], 'required'],
            [['user_id', 'seen', 'source_pk', 'space_id', 'emailed', 'created_by', 'updated_by', 'desktop_notified', 'originator_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['class', 'source_class'], 'string', 'max' => 100]
        ];
    }

    /**
     * @return \humhub\modules\notification\components\BaseNotification
     */
    public function getClass()
    {
        if (class_exists($this->class)) {
            $object = new $this->class;
            Yii::configure($object, [
                'source' => $this->getSourceObject(),
                'originator' => $this->originator,
                'record' => $this,
            ]);
            return $object;
        }
        return null;
    }

    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    public function getOriginator()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'originator_user_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_id']);
    }

    public function getSourceObject()
    {
        $sourceClass = $this->source_class;
        if ($sourceClass != "") {
            return $sourceClass::findOne(['id' => $this->source_pk]);
        }
        return null;
    }

}
