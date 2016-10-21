<?php

namespace humhub\modules\space\models;

use Yii;


/**
 * This is the model class for table "space_module".
 *
 * @property integer $id
 * @property string $module_id
 * @property integer $space_id
 * @property integer $state
 */
class Module extends \yii\db\ActiveRecord
{

    private static $_states = array();

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_FORCE_ENABLED = 2;
    const STATES_CACHE_ID_PREFIX = 'space_module_states_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_module';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'space_id'], 'required'],
            [['space_id', 'state'], 'integer'],
            [['module_id'], 'string', 'max' => 255]
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
            'space_id' => 'Space ID',
            'state' => 'State',
        ];
    }

    public function beforeSave($insert)
    {

        if ($this->space_id == "") {
            $this->space_id = 0;
        }

        Yii::$app->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->space_id);

        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->space_id);

        return parent::beforeDelete();
    }

    /**
     * Returns an array of moduleId and the their states (enabled, disabled, force enabled)
     * for given space id. If space id is 0 or empty, the default states will be returned.
     *
     * @param int $spaceId
     * @return array State of Module Ids
     */
    public static function getStates($spaceId = 0)
    {
        if (isset(self::$_states[$spaceId])) {
            return self::$_states[$spaceId];
        }

        $states = Yii::$app->cache->get(self::STATES_CACHE_ID_PREFIX . $spaceId);
        if ($states === false) {
            $states = array();
            foreach (self::find()->where(['space_id' => $spaceId])->all() as $spaceModule) {
                $states[$spaceModule->module_id] = $spaceModule->state;
            }
            Yii::$app->cache->set(self::STATES_CACHE_ID_PREFIX . $spaceId, $states);
        }

        self::$_states[$spaceId] = $states;

        return self::$_states[$spaceId];
    }

    public function getSpace()
    {
        return $this->hasOne(Space::className(), ['id' => 'space_id']);
    }

}
