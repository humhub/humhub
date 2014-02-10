<?php

/**
 * This is the model class for table "wall_entry".
 *
 * The followings are the available columns in table 'wall_entry':
 * @property integer $id
 * @property integer $wall_id
 * @property integer $content_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property Wall $wall
 *
 * @package humhub.modules_core.wall.models
 */
class WallEntry extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return WallEntry the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'wall_entry';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('wall_id, content_id', 'required'),
            array('wall_id, content_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'wall' => array(self::BELONGS_TO, 'Wall', 'wall_id'),
            'content' => array(self::BELONGS_TO, 'Content', 'content_id'),
        );
    }

    /**
     * Called before this entry will deleted
     *
     * @return type
     */
    public function beforeDelete() {
        $cacheId = "wallEntryCount_" . $this->wall_id;
        Yii::app()->cache->delete($cacheId);

        return parent::beforeDelete();
    }

    /**
     * Insert or Update this wall entry
     *
     * @return type
     */
    public function save($runValidation = true, $attributes = NULL) {
        $ret = parent::save($runValidation, $attributes);

        $cacheId = "wallEntryCount_" . $this->wall_id;
        Yii::app()->cache->delete($cacheId);

        return $ret;
    }

    /**
     * Counts wall entry for given wall id
     * Except Activities!
     *
     * @param type $wallId
     * @return type
     */
    /*
      public static function getWallEntryCount($wallId, $ignoreObjectModels = array()) {

      return 0;

      $condition = "";
      if (count($ignoreObjectModels) != 0) {

      foreach ($ignoreObjectModels as $model) {
      $condition .= 'object_model != "' . $model . '" AND ';
      }
      $condition .= ' 1'; // fix sql
      }

      $cacheId = "wallEntryCount_" . $wallId . $condition;
      $cacheValue = Yii::app()->cache->get($cacheId);

      if ($cacheValue === false) {
      $newCacheValue = WallEntry::model()->countByAttributes(array('wall_id' => $wallId), $condition);
      Yii::app()->cache->set($cacheId, $newCacheValue, HSetting::Get('expireTime', 'cache'));
      return $newCacheValue;
      } else {
      return $cacheValue;
      }
      }
     */
}