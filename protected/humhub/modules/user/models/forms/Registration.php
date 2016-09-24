<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use humhub\compat\HForm;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\GroupUser;

/**
 * Description of Registration
 *
 * @author luke
 */
class Registration extends HForm
{

    /**
     * @var boolean show password creation form
     */
    public $enablePasswordForm = true;

    /**
     * @var boolean show e-mail field
     */
    public $enableEmailField = false;

    /**
     * @var boolean|null require user approval by admin after registration.
     */
    public $enableUserApproval = false;

    /**
     * @var User
     */
    private $_user = null;

    /**
     * @var Password
     */
    private $_password = null;

    /**
     * @var Group Id
     */
    private $_groupUser = null;

    /**
     * @var Profile
     */
    private $_profile = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->getModule('user')->settings->get('auth.needApproval')) {
            $this->enableUserApproval = true;
        } else {
            $this->enableUserApproval = false;
        }

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function render($form)
    {
        $this->setFormDefinition();
        return parent::render($form);
    }

    /**
     * Builds HForm Definition to automatically build form output
     */
    protected function setFormDefinition()
    {
        $this->definition = [];
        $this->definition['elements'] = [];
        $this->definition['elements']['User'] = $this->getUserFormDefinition();
        $this->definition['elements']['GroupUser'] = $this->getGroupFormDefinition();
        if ($this->enablePasswordForm) {
            $this->definition['elements']['Password'] = $this->getPasswordFormDefinition();
        }
        $this->definition['elements']['Profile'] = array_merge(array('type' => 'form'), $this->getProfile()->getFormDefinition());
        $this->definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'class' => 'btn btn-primary',
                'label' => Yii::t('UserModule.controllers_AuthController', 'Create account'),
            ),
        );
    }

    /**
     * Create User Model form fields required for registration
     *
     * @return array form definition
     */
    protected function getUserFormDefinition()
    {
        $form = array(
            'type' => 'form',
            'title' => Yii::t('UserModule.controllers_AuthController', 'Account'),
            'elements' => [],
        );

        $form['elements']['username'] = [
            'type' => 'text',
            'class' => 'form-control',
            'maxlength' => 25,
        ];
        if ($this->enableEmailField) {
            $form['elements']['email'] = [
                'type' => 'text',
                'class' => 'form-control',
            ];
        }

        return $form;
    }

    /**
     * Create Password Model form fields required for registration
     *
     * @return array form definition
     */
    protected function getPasswordFormDefinition()
    {
        return array(
            'type' => 'form',
            'elements' => array(
                'newPassword' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
                'newPasswordConfirm' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
            ),
        );
    }

    protected function getGroupFormDefinition()
    {
        $groupModels = \humhub\modules\user\models\Group::getRegistrationGroups();
        $defaultUserGroup = Yii::$app->getModule('user')->settings->get('auth.defaultUserGroup');
        $groupFieldType = "dropdownlist";

        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
        }

        return [
            'type' => 'form',
            'elements' => [
                'group_id' => [
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => ArrayHelper::map($groupModels, 'id', 'name'),
                    'value' => $defaultUserGroup,
                ]
            ]
        ];
    }

    /**
     * Set models User, Profile and Password to Form
     */
    protected function setModels()
    {
        // Set Models
        $this->models['User'] = $this->getUser();
        $this->models['Profile'] = $this->getProfile();
        $this->models['GroupUser'] = $this->getGroupUser();
        if ($this->enablePasswordForm) {
            $this->models['Password'] = $this->getPassword();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        // Ensure Models
        $this->setModels();

        // Remove optional group assignment before validation
        // GroupUser assignment is optional and will be validated on save
        $groupUser = $this->models['GroupUser'];
        unset($this->models['GroupUser']);
        $status = parent::validate();
        $this->models['GroupUser'] = $groupUser;

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function submitted($buttonName = "")
    {
        // Ensure Models
        $this->setModels();

        return parent::submitted($buttonName);
    }

    /**
     * Registers users
     *
     * @return boolean state
     */
    public function register(\yii\authclient\ClientInterface $authClient = null)
    {
        $this->models['User']->language = Yii::$app->language;
        if ($this->enableUserApproval) {
            $this->models['User']->status = User::STATUS_NEED_APPROVAL;
            $this->models['User']->registrationGroupId = $this->models['GroupUser']->group_id;
        }

        if ($this->models['User']->save()) {

            // Save User Profile
            $this->models['Profile']->user_id = $this->models['User']->id;
            $this->models['Profile']->save();

            if ($this->models['GroupUser']->validate()) {
                $this->models['GroupUser']->user_id = $this->models['User']->id;
                $this->models['GroupUser']->save();
            }

            if ($this->enablePasswordForm) {
                // Save User Password
                $this->models['Password']->user_id = $this->models['User']->id;
                $this->models['Password']->setPassword($this->models['Password']->newPassword);
                $this->models['Password']->save();
            }

            if ($authClient !== null) {
                \humhub\modules\user\authclient\AuthClientHelpers::storeAuthClientForUser($authClient, $this->models['User']);
                $authClient->trigger(\humhub\modules\user\authclient\BaseClient::EVENT_CREATE_USER, new \yii\web\UserEvent(['identity' => $this->models['User']]));
            }

            return true;
        }

        return false;
    }

    /**
     * Returns User model
     *
     * @return User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = new User();
            if ($this->enableEmailField) {
                $this->_user->scenario = 'registration_email';
            } else {
                $this->_user->scenario = 'registration';
            }
        }

        return $this->_user;
    }

    /**
     * Returns Profile model
     *
     * @return Profile
     */
    public function getProfile()
    {
        if ($this->_profile === null) {
            $this->_profile = $this->getUser()->profile;
            $this->_profile->scenario = 'registration';
        }

        return $this->_profile;
    }

    /**
     * Returns Password model
     *
     * @return Password
     */
    public function getPassword()
    {
        if ($this->_password === null) {
            $this->_password = new Password();
            $this->_password->scenario = 'registration';
        }

        return $this->_password;
    }

    /**
     * Returns Password model
     *
     * @return Password
     */
    public function getGroupUser()
    {
        if ($this->_groupUser === null) {
            $this->_groupUser = new GroupUser();
            $this->_groupUser->scenario = GroupUser::SCENARIO_REGISTRATION;

            // assign default value for group_id
            $registrationGroups = \humhub\modules\user\models\Group::getRegistrationGroups();
            if (count($registrationGroups) == 1) {
                $this->_groupUser->group_id = $registrationGroups[0]->id;
            }
        }

        return $this->_groupUser;
    }

}
