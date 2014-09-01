<?php

/**
 * This is the model class for table "user_module".
 * It holds all activated user modules.
 *
 * When user_id is set to 0 the record defines the user default setting.
 * 
 * The followings are the available columns in table 'user_module':
 * @property integer $id
 * @property string $module_id
 * @property integer $user_id
 * @property integer $state
 *
 * @author Luke
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class UserApplicationModule extends HActiveRecord
{

    private static $_states = array();

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_FORCE_ENABLED = 2;
    const STATES_CACHE_ID_PREFIX = 'user_module_states_';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserApplicationModule the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user_module';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('module_id, user_id', 'required'),
            array('user_id, state', 'numerical', 'integerOnly' => true),
            array('module_id', 'length', 'max' => 255),
        );
    }

    public function beforeSave()
    {

        if ($this->user_id == "") {
            $this->user_id = 0;
        }

        Yii::app()->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->user_id);

        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        Yii::app()->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->user_id);

        return parent::beforeDelete();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
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

        $states = Yii::app()->cache->get(self::STATES_CACHE_ID_PREFIX . $userId);
        if ($states === false) {
            $states = array();
            foreach (UserApplicationModule::model()->findAllByAttributes(array('user_id' => $userId)) as $userModule) {
                $states[$userModule->module_id] = $userModule->state;
            }
            Yii::app()->cache->set(self::STATES_CACHE_ID_PREFIX . $userId, $states);
        }

        self::$_states[$userId] = $states;

        return self::$_states[$userId];
    }

}
