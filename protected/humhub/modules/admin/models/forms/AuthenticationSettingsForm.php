<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\libs\DynamicConfig;
use humhub\modules\topic\jobs\ConvertTopicsToGlobalJob;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\base\Model;

/**
 * AuthenticationSettingsForm
 *
 * @since 0.5
 */
class AuthenticationSettingsForm extends Model
{
    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInviteByEmail;
    public $internalUsersCanInviteByLink;
    public $showRegistrationUserGroup;
    public $blockUsers;
    public $hideOnlineStatus;
    public $defaultUserIdleTimeoutSec;
    public $allowGuestAccess;
    public $defaultUserProfileVisibility;
    public $registrationSendMessageMailContent;
    public $registrationApprovalMailContent;
    public $registrationDenialMailContent;
    public $allowUserTopics = true;

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
        $this->hideOnlineStatus = $settingsManager->get('auth.hideOnlineStatus');
        $this->defaultUserIdleTimeoutSec = $settingsManager->get('auth.defaultUserIdleTimeoutSec');
        $this->allowGuestAccess = $settingsManager->get('auth.allowGuestAccess');
        $this->defaultUserProfileVisibility = $settingsManager->get('auth.defaultUserProfileVisibility');
        $this->registrationSendMessageMailContent = $settingsManager->get('auth.registrationSendMessageMailContent', ApproveUserForm::getDefaultSendMessageMailContent());
        $this->registrationApprovalMailContent = $settingsManager->get('auth.registrationApprovalMailContent', ApproveUserForm::getDefaultApprovalMessage());
        $this->registrationDenialMailContent = $settingsManager->get('auth.registrationDenialMailContent', ApproveUserForm::getDefaultDeclineMessage());
        $this->allowUserTopics = $settingsManager->get('auth.allowUserTopics', true);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['internalUsersCanInviteByEmail', 'internalUsersCanInviteByLink', 'internalAllowAnonymousRegistration', 'internalRequireApprovalAfterRegistration', 'allowGuestAccess', 'showRegistrationUserGroup', 'blockUsers', 'hideOnlineStatus'], 'boolean'],
            ['defaultUserProfileVisibility', 'in', 'range' => array_keys(User::getVisibilityOptions(false))],
            ['defaultUserIdleTimeoutSec', 'integer', 'min' => 20],
            [['registrationSendMessageMailContent', 'registrationApprovalMailContent', 'registrationDenialMailContent'], 'string'],
            [['allowUserTopics'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.user', 'Post-registration approval required'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.user', 'New users can register'),
            'internalUsersCanInviteByEmail' => Yii::t('AdminModule.user', 'Members can invite external users by email'),
            'internalUsersCanInviteByLink' => Yii::t('AdminModule.user', 'Members can invite external users by link'),
            'showRegistrationUserGroup' => Yii::t('AdminModule.user', 'Show group selection at registration'),
            'blockUsers' => Yii::t('AdminModule.user', 'Allow users to block each other'),
            'hideOnlineStatus' => Yii::t('AdminModule.user', 'Hide online status of users'),
            'defaultUserIdleTimeoutSec' => Yii::t('AdminModule.user', 'Default user idle timeout, auto-logout (in seconds, optional)'),
            'allowGuestAccess' => Yii::t('AdminModule.user', 'Allow visitors limited access to content without an account (Adds visibility: "Guest")'),
            'defaultUserProfileVisibility' => Yii::t('AdminModule.user', 'Default user profile visibility'),
            'registrationSendMessageMailContent' => Yii::t('AdminModule.user', 'Default content of the email when sending a message to the user'),
            'registrationApprovalMailContent' => Yii::t('AdminModule.user', 'Default content of the registration approval email'),
            'registrationDenialMailContent' => Yii::t('AdminModule.user', 'Default content of the registration denial email'),
            'allowUserTopics' => Yii::t('AdminModule.user', 'Allow individual topics on profiles'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.user', 'If enabled, the Group Manager will need to approve registration.'),
        ];
    }

    /**
     * Saves the form
     *
     * @return bool
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
        $settingsManager->set('auth.hideOnlineStatus', $this->hideOnlineStatus);
        $settingsManager->set('auth.defaultUserIdleTimeoutSec', $this->defaultUserIdleTimeoutSec);
        $settingsManager->set('auth.allowGuestAccess', $this->allowGuestAccess);
        $settingsManager->set('auth.allowUserTopics', $this->allowUserTopics);

        if ($settingsManager->get('auth.allowGuestAccess')) {
            $settingsManager->set('auth.defaultUserProfileVisibility', $this->defaultUserProfileVisibility);
        }

        if ($settingsManager->get('auth.needApproval')) {
            if (empty($this->registrationSendMessageMailContent) || $this->registrationSendMessageMailContent === ApproveUserForm::getDefaultSendMessageMailContent()) {
                $this->registrationSendMessageMailContent = ApproveUserForm::getDefaultSendMessageMailContent();
                $settingsManager->delete('auth.registrationSendMessageMailContent');
            } else {
                $settingsManager->set('auth.registrationSendMessageMailContent', $this->registrationSendMessageMailContent);
            }

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

        if (!$this->allowUserTopics) {
            Yii::$app->queue->push(new ConvertTopicsToGlobalJob([
                'containerType' => User::class,
            ]));
        }

        DynamicConfig::rewrite();
        return true;
    }

}
