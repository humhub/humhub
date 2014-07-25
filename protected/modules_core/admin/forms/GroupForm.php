<?php

/**
 * GroupForm is used to modify group settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class GroupForm extends CFormModel {

    /**
     * @var integer id of the group
     */
    public $groupId;

    /**
     * @var string name of the group
     */
    public $name;

    /**
     * @var string description of the group
     */
    public $description;

    /**
     * @var string spacepicker of the group
     */
    public $defaultSpaceGuid;

    /**
     * @var string userpicker string for admin users
     */
    public $admins;

    /**
     * @var string Ldap Group DN
     */
    public $ldapDn;

    /**
     * Validation rules for group form
     *
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('groupId', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 45),
            array('ldapDn', 'length', 'max' => 255),
            array('description, admins', 'safe'),
            array('defaultSpaceGuid', 'checkSpaceGuid'),
            array('name, description', 'required'),
        );
    }

    /**
     * Declares customized attribute labels.
     *
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'name' => Yii::t('AdminModule.forms_GroupForm', 'Name'),
            'description' => Yii::t('AdminModule.forms_GroupForm', 'Description'),
            'defaultSpaceGuid' => Yii::t('AdminModule.forms_GroupForm', 'Default space'),
            'admins' => Yii::t('AdminModule.forms_GroupForm', 'Administrator users'),
            'ldapDn' => Yii::t('AdminModule.forms_GroupForm', 'Ldap DN'),
        );
    }

    /**
     * Sets an group for this Form
     *
     * This populates all values.
     */
    public function setGroup($group) {

        // Init current values
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->ldapDn = $group->ldap_dn;

        $this->admins = "";
        foreach ($group->admins as $admin) {
            $this->admins .= $admin->user->guid . ",";
        }
        $this->admins = rtrim($this->admins, ',');

        if ($group->space) {
            $this->defaultSpaceGuid = $group->space->guid;
        }

    }


    /**
     * Parses the admin attribute and returns an array of admin user object
     */
    public function getAdminUsers() {

        $adminUsers = array();

        // Generate an array of @guid values
        $admins = explode(",", $this->admins);

        foreach ($admins as $adminGuid) {

            // Ensure guids valid characters
            $adminGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $adminGuid);

            // Try load user
            $user = User::model()->findByAttributes(array('guid' => $adminGuid));
            if ($user != null) {
                $adminUsers[] = $user;
            }
        }

        return $adminUsers;
    }

/**
     * This validator function checks the defaultSpaceGuid.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkSpaceGuid($attribute, $params) {

        if ($this->defaultSpaceGuid != "") {

            // Some Cleanups
            $this->defaultSpaceGuid = rtrim($this->defaultSpaceGuid, ',');
            $this->defaultSpaceGuid = trim($this->defaultSpaceGuid);

            $space = Space::model()->findByAttributes(array('guid' => $this->defaultSpaceGuid));

            if ($space != null) {
                $this->defaultSpaceGuid = $space->guid;
            } else {
                $this->addError($attribute, Yii::t('AdminModule.forms_GroupForm', "Invalid space"));
            }
        }
    }

}