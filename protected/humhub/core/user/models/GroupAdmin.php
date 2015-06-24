<?php

/**
 * This is the model class for table "group_admin".
 *
 * The table is used to map admin users to a group.
 *
 * The followings are the available columns in table 'group_admin':
 * @property integer $id
 * @property integer $user_id
 * @property integer $group_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class GroupAdmin extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return GroupAdmin the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'group_admin';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, group_id', 'required'),
            array('user_id, group_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, group_id, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
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
            'group' => array(self::BELONGS_TO, 'Group', 'group_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'group_id' => 'Group',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('group_id', $this->group_id);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Used in Approvals, to get Groups Filter
     *
     * @return Array of groups
     */
    public static function gridItems() {
        $items = array();

        $selectedAdminGroups = array();

        if (!Yii::app()->user->isAdmin()) {
            foreach (GroupAdmin::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id)) as $ga) {
                $selectedAdminGroups[] = $ga->group_id;
            }
        }

        foreach (Group::model()->findAll() as $g) {

            // Bypass group without rights
            if (!Yii::app()->user->isAdmin() && !in_array($g->id, $selectedAdminGroups)) {
                continue;
            }

            $items[$g->id] = $g->name;
        }

        return $items;
    }

}