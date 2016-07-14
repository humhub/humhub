<?php

namespace humhub\modules\user\models;

use Yii;

/**
 * This is the model class for table "user_module".
 * It hold all enabled modules for a user.
 *
 * @property integer $id
 * @property string $module_id
 * @property integer $user_id
 * @property integer $state
 */
class Module extends \yii\db\ActiveRecord
{

    private static $_states = array();

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_FORCE_ENABLED = 2;
    const STATES_CACHE_ID_PREFIX = 'user_module_states_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_module';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'user_id'], 'required'],
            [['user_id', 'state'], 'integer'],
            [['module_id'], 'string', 'max' => 255],
            [['user_id', 'module_id'], 'unique', 'targetAttribute' => ['user_id', 'module_id'], 'message' => 'The combination of Module ID and User ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_id' => 'Module ID',
            'user_id' => 'User ID',
            'state' => 'State',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->user_id == '') {
            $this->user_id = 0;
        }

        Yii::$app->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->user_id);
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->user_id);
        return parent::beforeDelete();
    }

    /**
     * Returns an array of moduleId and the their states (enabled, disabled, force enabled)
     * for given user  id. If space id is 0 or empty, the default states will be returned.
     *
     * @param int $userId
     * @return array State of Module Ids
     */
    public static function getStates($userId = 0)
    {
        if (isset(self::$_states[$userId])) {
            return self::$_states[$userId];
        }

        $states = Yii::$app->cache->get(self::STATES_CACHE_ID_PREFIX . $userId);
        if ($states === false) {
            $states = array();
            foreach (self::find()->where(['user_id' => $userId])->all() as $userModule) {
                $states[$userModule->module_id] = $userModule->state;
            }
            Yii::$app->cache->set(self::STATES_CACHE_ID_PREFIX . $userId, $states);
        }

        self::$_states[$userId] = $states;

        return self::$_states[$userId];
    }

    /**
     * Related user
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
