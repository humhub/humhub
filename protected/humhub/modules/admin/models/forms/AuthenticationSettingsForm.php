<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\libs\DynamicConfig;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

/**
 * AuthenticationSettingsForm
 *
 * @since 0.5
 */
class AuthenticationSettingsForm extends \yii\base\Model
{
    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInviteByEmail;
    public $internalUsersCanInviteByLink;
    public $showRegistrationUserGroup;
    public $blockUsers;
    public $showOnlineStatus;
    public $defaultUserIdleTimeoutSec;
    public $allowGuestAccess;
    public $showCaptureInRegisterForm;
    public $defaultUserProfileVisibility;
    public $registrationApprovalMailContent;
    public $registrationDenialMailContent;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $settingsManager = $module->settings;

        $this->internalUsersCanInviteByEmail = $settingsManager->get('auth.internalUsersCanInviteByEmail');
        $this->internalUsersCanInviteByLink = $settingsManager->get('auth.internalUsersCanInviteByLink');
        $this->internalRequireApprovalAfterRegistration = $settingsManager->get('auth.needApproval');
        $this->internalAllowAnonymousRegistration = $settingsManager->get('auth.anonymousRegistration');
        $this->showRegistrationUserGroup = $settingsManager->get('auth.showRegistrationUserGroup');
        $this->blockUsers = $module->allowBlockUsers();
        $this->showOnlineStatus = $settingsManager->get('auth.showOnlineStatus');
        $this->defaultUserIdleTimeoutSec = $settingsManager->get('auth.defaultUserIdleTimeoutSec');
        $this->allowGuestAccess = $settingsManager->get('auth.allowGuestAccess');
        $this->showCaptureInRegisterForm = $settingsManager->get('auth.showCaptureInRegisterForm');
        $this->defaultUserProfileVisibility = $settingsManager->get('auth.defaultUserProfileVisibility');
        $this->registrationApprovalMailContent = $settingsManager->get('auth.registrationApprovalMailContent', ApproveUserForm::getDefaultApprovalMessage());
        $this->registrationDenialMailContent = $settingsManager->get('auth.registrationDenialMailContent', ApproveUserForm::getDefaultDeclineMessage());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['internalUsersCanInviteByEmail', 'internalUsersCanInviteByLink', 'internalAllowAnonymousRegistration', 'internalRequireApprovalAfterRegistration', 'allowGuestAccess', 'showCaptureInRegisterForm', 'showRegistrationUserGroup', 'blockUsers', 'showOnlineStatus'], 'boolean'],
            ['defaultUserProfileVisibility', 'in', 'range' => array_keys(User::getVisibilityOptions(false))],
            ['defaultUserIdleTimeoutSec', 'integer', 'min' => 20],
            [['registrationApprovalMailContent', 'registrationDenialMailContent'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.user', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.user', 'New users can register'),
            'internalUsersCanInviteByEmail' => Yii::t('AdminModule.user', 'Members can invite external users by email'),
            'internalUsersCanInviteByLink' => Yii::t('AdminModule.user', 'Members can invite external users by link'),
            'showRegistrationUserGroup' => Yii::t('AdminModule.user', 'Show group selection at registration'),
            'blockUsers' => Yii::t('AdminModule.user', 'Allow users to block each other'),
            'showOnlineStatus' => Yii::t('AdminModule.user', 'Show online status on the profile image'),
            'defaultUserIdleTimeoutSec' => Yii::t('AdminModule.user', 'Default user idle timeout, auto-logout (in seconds, optional)'),
            'allowGuestAccess' => Yii::t('AdminModule.user', 'Allow visitors limited access to content without an account (Adds visibility: "Guest")'),
            'showCaptureInRegisterForm' => Yii::t('AdminModule.user', 'Include captcha in registration form'),
            'defaultUserProfileVisibility' => Yii::t('AdminModule.user', 'Default user profile visibility'),
            'registrationApprovalMailContent' => Yii::t('AdminModule.user', 'Default content of the registration approval email'),
            'registrationDenialMailContent' => Yii::t('AdminModule.user', 'Default content of the registration denial email'),
        ];
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $settingsManager = $module->settings;

        $settingsManager->set('auth.internalUsersCanInviteByEmail', $this->internalUsersCanInviteByEmail);
        $settingsManager->set('auth.internalUsersCanInviteByLink', $this->internalUsersCanInviteByLink);
        $settingsManager->set('auth.needApproval', $this->internalRequireApprovalAfterRegistration);
        $settingsManager->set('auth.anonymousRegistration', $this->internalAllowAnonymousRegistration);
        $settingsManager->set('auth.showRegistrationUserGroup', $this->showRegistrationUserGroup);
        $settingsManager->set('auth.blockUsers', $this->blockUsers);
        $settingsManager->set('auth.showOnlineStatus', $this->showOnlineStatus);
        $settingsManager->set('auth.defaultUserIdleTimeoutSec', $this->defaultUserIdleTimeoutSec);
        $settingsManager->set('auth.allowGuestAccess', $this->allowGuestAccess);

        if ($settingsManager->get('auth.allowGuestAccess')) {
            $settingsManager->set('auth.defaultUserProfileVisibility', $this->defaultUserProfileVisibility);
        }

        if ($settingsManager->get('auth.anonymousRegistration')) {
            $settingsManager->set('auth.showCaptureInRegisterForm', $this->showCaptureInRegisterForm);
        }

        if ($settingsManager->get('auth.needApproval')) {
            if (empty($this->registrationApprovalMailContent) || $this->registrationApprovalMailContent === ApproveUserForm::getDefaultApprovalMessage()) {
                $this->registrationApprovalMailContent = ApproveUserForm::getDefaultApprovalMessage();
                $settingsManager->delete('auth.registrationApprovalMailContent');
            } else {
                $settingsManager->set('auth.registrationApprovalMailContent', $this->registrationApprovalMailContent);
            }

            if (empty($this->registrationDenialMailContent) || $this->registrationDenialMailContent === ApproveUserForm::getDefaultDeclineMessage()) {
                $this->registrationDenialMailContent = ApproveUserForm::getDefaultDeclineMessage();
                $settingsManager->delete('auth.registrationDenialMailContent');
            } else {
                $settingsManager->set('auth.registrationDenialMailContent', $this->registrationDenialMailContent);
            }
        }

        DynamicConfig::rewrite();
        return true;
    }

}
