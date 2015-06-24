<?php

/**
 * This is the model class for table "user_http_session".
 *
 * The followings are the available columns in table 'user_http_session':
 * @property string $id
 * @property integer $expire
 * @property integer $user_id
 * @property string $data
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */
class UserHttpSession extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserHttpSession the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_http_session';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id', 'required'),
            array('expire, user_id', 'numerical', 'integerOnly' => true),
            array('id', 'length', 'max' => 255),
            array('data', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, expire, user_id, data', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'expire' => 'Expire',
            'user_id' => 'User',
            'data' => 'Data',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('expire', $this->expire);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('data', $this->data, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

}