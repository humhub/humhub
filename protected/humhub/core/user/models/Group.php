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
 * @property integer $can_create_public_spaces
 * @property integer $can_create_private_spaces
 *
 * The followings are the available model relations:
 * @property User[] $users
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Group extends HActiveRecord
{

    public $adminGuids;
    public $defaultSpaceGuid;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Group the static model class
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
        return 'group';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('can_create_public_spaces, can_create_private_spaces', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 45),
            array('ldap_dn', 'length', 'max' => 255),
            array('description, adminGuids, defaultSpaceGuid', 'safe'),
            array('id, name, description', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'users' => array(self::HAS_MANY, 'User', 'group_id'),
            'admins' => array(self::HAS_MANY, 'GroupAdmin', 'group_id'),
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('UserModule.models_Group', 'ID'),
            'name' => Yii::t('UserModule.models_Group', 'Name'),
            'space_id' => Yii::t('UserModule.models_Group', 'Default Space'),
            'description' => Yii::t('UserModule.models_Group', 'Description'),
            'ldap_dn' => Yii::t('UserModule.models_Group', 'LDAP DN'),
            'adminGuids' => Yii::t('UserModule.models_Group', 'Group Administrators'),
            'defaultSpaceGuid' => Yii::t('UserModule.models_Group', 'Default Space'),
            'can_create_public_spaces' => Yii::t('UserModule.models_Group', 'Members can create public spaces'),
            'can_create_private_spaces' => Yii::t('UserModule.models_Group', 'Members can create private spaces'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Deletes a user including all dependencies
     *
     * @return boolean
     */
    public function delete()
    {
        GroupAdmin::model()->deleteAllByAttributes(array('group_id' => $this->id));

        // Clear Default User Group for Registration if needed
        if (HSetting::Get('defaultUserGroup', 'authentication_internal') == $this->id) {
            HSetting::Set('defaultUserGroup', '', 'authentication_internal');
        }

        return parent::delete();
    }

    /**
     * Helper Function return the name of a group by Id
     *
     * @param type $id
     * @return string
     */
    public static function getGroupNameById($id)
    {
        $group = Group::model()->findByPk($id);
        if ($group != null) {
            return $group->name;
        }
        return "undefined";
    }

    public function beforeSave()
    {

        // When on edit form scenario, save also defaultSpaceGuid/adminGuids
        if ($this->scenario == 'edit') {
            if ($this->defaultSpaceGuid == "") {
                $this->space_id = "";
            } else {
                $space = Space::model()->findByAttributes(array('guid' => rtrim($this->defaultSpaceGuid, ',')));
                if ($space !== null) {
                    $this->space_id = $space->id;
                }
            }
        }


        return parent::beforeSave();
    }

    public function afterSave()
    {
        if ($this->scenario == 'edit') {
            GroupAdmin::model()->deleteAllByAttributes(array('group_id' => $this->id));

            $adminUsers = array();

            foreach (explode(",", $this->adminGuids) as $adminGuid) {

                // Ensure guids valid characters
                $adminGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $adminGuid);

                // Try load user
                $user = User::model()->findByAttributes(array('guid' => $adminGuid));
                if ($user != null) {
                    $groupAdmin = new GroupAdmin;
                    $groupAdmin->user_id = $user->id;
                    $groupAdmin->group_id = $this->id;
                    $groupAdmin->save();
                }
            }
        }
    }

    public function populateDefaultSpaceGuid()
    {
        $defaultSpace = Space::model()->findByPk($this->space_id);
        if ($defaultSpace !== null) {
            $this->defaultSpaceGuid = $defaultSpace->guid;
        }
    }

    public function populateAdminGuids()
    {
        $this->adminGuids = "";
        foreach ($this->admins as $admin) {
            $this->adminGuids .= $admin->user->guid . ",";
        }
    }

}
