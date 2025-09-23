<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\compat\HForm;
use humhub\modules\user\authclient\BaseClient;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientUserService;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;
use yii\web\UserEvent;

/**
 * Description of Registration
 *
 * @author luke
 */
class Registration extends HForm
{
    /**
     * @event \yii\web\UserEvent triggered after successful registration.
     */
    public const EVENT_AFTER_REGISTRATION = 'afterRegistration';

    /**
     * @var bool show password creation form
     */
    private $enablePasswordForm;

    /**
     * @var bool show checkbox to force to change password on first log in
     */
    private $enableMustChangePassword;

    /**
     * @var bool show e-mail field
     */
    private $enableEmailField;

    /**
     * @var bool|null require user approval by admin after registration.
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

    public function __construct(
        $definition = [],
        $primaryModel = null,
        array $config = [],
        bool $enableEmailField = false,
        bool $enablePasswordForm = true,
        bool $enableMustChangePassword = false,
    ) {
        $this->enableEmailField = $enableEmailField;
        $this->enablePasswordForm = $enablePasswordForm;
        $this->enableMustChangePassword = $enableMustChangePassword;

        parent::__construct($definition, $primaryModel, $config);
    }

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

        $this->setFormDefinition();

        parent::init();
    }

    /**
     * Builds HForm Definition to automatically build form output
     */
    protected function setFormDefinition()
    {
        if (!isset($this->definition['elements']) || !is_array($this->definition['elements'])) {
            $this->definition['elements'] = [];
        }
        $this->definition['elements']['User'] = $this->getUserFormDefinition();
        $this->definition['elements']['GroupUser'] = $this->getGroupFormDefinition();
        if ($this->enablePasswordForm) {
            $this->definition['elements']['Password'] = $this->getPasswordFormDefinition();
        }
        $this->definition['elements']['Profile'] = array_merge(
            ['type' => 'form'],
            $this->getProfile()->getFormDefinition(),
        );
        $this->definition['buttons'] = [
            'save' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
                'label' => Yii::t('UserModule.auth', 'Create account'),
            ],
        ];
    }

    /**
     * Create User Model form fields required for registration
     *
     * @return array form definition
     */
    protected function getUserFormDefinition()
    {
        $form = [
            'type' => 'form',
            'title' => Yii::t('UserModule.auth', 'Account'),
            'elements' => [],
        ];

        $form['elements']['username'] = [
            'type' => 'text',
            'class' => 'form-control',
            'maxlength' => 25,
        ];
        $form['elements']['time_zone'] = [
            'type' => 'hidden',
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
        $form = [
            'type' => 'form',
            'elements' => [
                'newPassword' => [
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
                'newPasswordConfirm' => [
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
            ],
        ];

        if ($this->enableMustChangePassword) {
            $form['elements']['mustChangePassword'] = [
                'type' => 'checkbox',
                'class' => 'form-control',
            ];
        }

        return $form;
    }

    protected function getGroupFormDefinition()
    {
        $groupModels = Group::getRegistrationGroups($this->getUser());

        $groupFieldType = (Yii::$app->getModule('user')->settings->get('auth.showRegistrationUserGroup') && count(
            $groupModels,
        ) > 1)
            ? 'dropdownlist'
            : 'hidden'; // TODO: Completely hide the element instead of current <input type="hidden">

        return [
            'type' => 'form',
            'elements' => [
                'group_id' => [
                    'label' => Yii::t('UserModule.auth', 'Group'),
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => ArrayHelper::map($groupModels, 'id', 'name'),
                    'value' => Yii::$app->getModule('user')->getDefaultGroupId(),
                ],
            ],
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
            if (!isset($this->models['Password']->mustChangePassword)) {
                // Enable the checkbox by default on new user form:
                $this->models['Password']->mustChangePassword = true;
            }
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
     * @return bool state
     */
    public function register(ClientInterface $authClient = null)
    {
        if (!$this->validate()) {
            return false;
        }

        $this->models['User']->language = Yii::$app->i18n->getAllowedLanguage();
        if ($this->enableUserApproval) {
            $this->models['User']->status = User::STATUS_NEED_APPROVAL;
            $this->models['User']->registrationGroupId = $this->models['GroupUser']->group_id;
        }

        if ($this->models['User']->save()) {
            // Save User Profile
            $this->models['Profile']->user_id = $this->models['User']->id;
            $this->models['Profile']->save();

            $this->models['User']->populateRelation('profile', $this->models['Profile']);

            if ($this->models['GroupUser']->validate()) {
                $this->models['GroupUser']->user_id = $this->models['User']->id;
                $this->models['GroupUser']->save();
            }

            if ($this->enablePasswordForm) {
                // Save User Password
                $this->models['Password']->user_id = $this->models['User']->id;
                $this->models['Password']->setPassword($this->models['Password']->newPassword);
                if ($this->models['Password']->save()
                    && $this->enableMustChangePassword) {
                    $this->models['User']->setMustChangePassword($this->models['Password']->mustChangePassword);
                }
            }

            if ($authClient !== null) {
                (new AuthClientUserService($this->models['User']))->add($authClient);
                $authClient->trigger(
                    BaseClient::EVENT_CREATE_USER,
                    new UserEvent(['identity' => $this->models['User']]),
                );
            }

            $this->trigger(self::EVENT_AFTER_REGISTRATION, new UserEvent(['identity' => $this->models['User']]));

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
            $registrationGroups = Group::getRegistrationGroups($this->getUser());
            if (count($registrationGroups) == 1) {
                $this->_groupUser->group_id = $registrationGroups[0]->id;
            }
        }

        return $this->_groupUser;
    }


    public function getErrors()
    {
        $errors = [];

        if ($this->models['User']->hasErrors()) {
            $errors = array_merge($errors, $this->models['User']->getErrors());
        }

        if ($this->models['Profile']->hasErrors()) {
            $errors = array_merge($errors, $this->models['Profile']->getErrors());
        }

        return $errors;
    }
}
