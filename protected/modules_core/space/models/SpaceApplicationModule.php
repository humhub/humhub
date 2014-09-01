<?php

/**
 * This is the model class for table "space_module".
 * It holds all activated space modules.
 *
 * When space_id is set to 0 the record defines the space default setting.
 * 
 * The followings are the available columns in table 'space_module':
 * @property integer $id
 * @property string $module_id
 * @property integer $space_id
 * @property integer $state
 *
 * @author Luke
 * @package humhub.modules_core.space.models
 * @since 0.5
 */
class SpaceApplicationModule extends HActiveRecord
{

    private static $_states = array();

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_FORCE_ENABLED = 2;
    const STATES_CACHE_ID_PREFIX = 'space_module_states_';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SpaceApplicationModule the static model class
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
        return 'space_module';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('module_id, space_id', 'required'),
            array('space_id, state', 'numerical', 'integerOnly' => true),
            array('module_id', 'length', 'max' => 255),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
        );
    }

    public function beforeSave()
    {

        if ($this->space_id == "") {
            $this->space_id = 0;
        }

        Yii::app()->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->space_id);

        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        Yii::app()->cache->delete(self::STATES_CACHE_ID_PREFIX . $this->space_id);

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

        $states = Yii::app()->cache->get(self::STATES_CACHE_ID_PREFIX . $spaceId);
        if ($states === false) {
            $states = array();
            foreach (SpaceApplicationModule::model()->findAllByAttributes(array('space_id' => $spaceId)) as $spaceModule) {
                $states[$spaceModule->module_id] = $spaceModule->state;
            }
            Yii::app()->cache->set(self::STATES_CACHE_ID_PREFIX . $spaceId, $states);
        }

        self::$_states[$spaceId] = $states;

        return self::$_states[$spaceId];
    }

}
