<?php

/**
 * This is the model class for table "group".
 *
 * The followings are the available columns in table 'group':
 * @property integer $id
 * @property integer $space_id
 * @property string $name
 * @property string $description
 * @property string $ldap_dn
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property User[] $users
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Group extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Group the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'group';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 45),
            array('ldap_dn', 'length', 'max' => 255),
            array('space_id', 'checkSpaceId'),
            array('description, created_at, updated_at, admins', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, description, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'users' => array(self::HAS_MANY, 'User', 'group_id'),
            'admins' => array(self::HAS_MANY, 'GroupAdmin', 'group_id'),
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('base', 'ID'),
            'name' => Yii::t('base', 'Name'),
            'space_id' => Yii::t('base', 'Space ID or GUID'),
            'description' => Yii::t('base', 'Description'),
            'ldap_dn' => Yii::t('base', 'LDAP DN'),
            'created_at' => Yii::t('base', 'Created at'),
            'created_by' => Yii::t('base', 'Created by'),
            'updated_at' => Yii::t('base', 'Updated at'),
            'updated_by' => Yii::t('base', 'Updated by'),
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

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Deletes a user including all dependencies
     *
     * @return boolean
     */
    public function delete() {
        GroupAdmin::model()->deleteAllByAttributes(array('group_id' => $this->id));
        return parent::delete();
    }

    /**
     * Helper Function return the name of a group by Id
     *
     * @param type $id
     * @return string
     */
    public static function getGroupNameById($id) {
        $group = Group::model()->findByPk($id);
        if ($group != null) {
            return $group->name;
        }
        return "undefined";
    }

    /**
     * This validator function checks the space_id.
     *
     * If a guid is entered in the id field, its automatically converted
     * into a id.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkSpaceId($attribute, $params) {

        if ($this->space_id != "") {

            $workspace = null;
            if (is_numeric($this->space_id)) {
                // Try find the Space by ID
                $workspace = Space::model()->findByAttributes(array('id' => $this->space_id));
            } else {
                // Try find by GUID when not found
                $workspace = Space::model()->findByAttributes(array('guid' => $this->space_id));
            }


            if ($workspace != null) {
                $this->space_id = $workspace->id;
            } else {
                $this->addError($attribute, Yii::t('AdminModule.base', "Invalid space ID"));
            }
        }
    }

}