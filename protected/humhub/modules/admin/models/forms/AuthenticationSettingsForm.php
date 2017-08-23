<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\libs\DynamicConfig;

/**
 * AuthenticationSettingsForm
 *
 * @since 0.5
 */
class AuthenticationSettingsForm extends \yii\base\Model
{

    const defaultRegistrationApprovalMailContent = 'Hello {displayName},<br><br>
Your account has been activated.<br><br>
Click here to login:<br>
<a href=\'{loginURL}\'>{loginURL}</a><br><br>

Kind Regards<br>
{AdminName}<br><br>';
    const defaultRegistrationDenialMailContent = 'Hello {displayName},<br><br>
Your account request has been declined.<br><br>

Kind Regards<br>
{AdminName}<br><br>';
    
    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;
    public $defaultUserGroup;
    public $defaultUserIdleTimeoutSec;
    public $allowGuestAccess;
    public $defaultUserProfileVisibility;
    public $registrationApprovalMailContent;
    public $registrationDenialMailContent;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->getModule('user')->settings;

        $this->internalUsersCanInvite = $settingsManager->get('auth.internalUsersCanInvite');
        $this->internalRequireApprovalAfterRegistration = $settingsManager->get('auth.needApproval');
        $this->internalAllowAnonymousRegistration = $settingsManager->get('auth.anonymousRegistration');
        $this->defaultUserGroup = $settingsManager->get('auth.defaultUserGroup');
        $this->defaultUserIdleTimeoutSec = $settingsManager->get('auth.defaultUserIdleTimeoutSec');
        $this->allowGuestAccess = $settingsManager->get('auth.allowGuestAccess');
        $this->defaultUserProfileVisibility = $settingsManager->get('auth.defaultUserProfileVisibility');
        $this->registrationApprovalMailContent = $settingsManager->get('auth.registrationApprovalMailContent', Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationApprovalMailContent));
        $this->registrationDenialMailContent = $settingsManager->get('auth.registrationDenialMailContent', Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationDenialMailContent));
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['internalUsersCanInvite', 'internalAllowAnonymousRegistration', 'internalRequireApprovalAfterRegistration', 'allowGuestAccess'], 'boolean'],
            ['defaultUserGroup', 'exist', 'targetAttribute' => 'id', 'targetClass' => \humhub\modules\user\models\Group::className()],
            ['defaultUserProfileVisibility', 'in', 'range' => [1, 2]],
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
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Anonymous users can register'),
            'internalUsersCanInvite' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Members can invite external users by email'),
            'defaultUserGroup' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user group for new users'),
            'defaultUserIdleTimeoutSec' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user idle timeout, auto-logout (in seconds, optional)'),
            'allowGuestAccess' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Allow limited access for non-authenticated users (guests)'),
            'defaultUserProfileVisibility' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user profile visibility'),
            'registrationApprovalMailContent' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default content of the registration approval email.'),
            'registrationDenialMailContent' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default content of the registration denial email.'),
        ];
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->getModule('user')->settings;

        $settingsManager->set('auth.internalUsersCanInvite', $this->internalUsersCanInvite);
        $settingsManager->set('auth.needApproval', $this->internalRequireApprovalAfterRegistration);
        $settingsManager->set('auth.anonymousRegistration', $this->internalAllowAnonymousRegistration);
        $settingsManager->set('auth.defaultUserGroup', $this->defaultUserGroup);
        $settingsManager->set('auth.defaultUserIdleTimeoutSec', $this->defaultUserIdleTimeoutSec);
        $settingsManager->set('auth.allowGuestAccess', $this->allowGuestAccess);

        if ($settingsManager->get('auth.allowGuestAccess')) {
            $settingsManager->set('auth.defaultUserProfileVisibility', $this->defaultUserProfileVisibility);
        }
        
        if ($settingsManager->get('auth.needApproval')) {
            if(empty($this->registrationApprovalMailContent) || (strcmp($this->registrationApprovalMailContent, Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationApprovalMailContent)) == 0)) {
                $this->registrationApprovalMailContent = Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationApprovalMailContent);
                $settingsManager->delete('auth.registrationApprovalMailContent');   
            } else {
                $settingsManager->set('auth.registrationApprovalMailContent', $this->registrationApprovalMailContent);
            }
            if(empty($this->registrationDenialMailContent) || strcmp($this->registrationDenialMailContent, Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationDenialMailContent)) == 0) {
                $this->registrationDenialMailContent = Yii::t('AdminModule.controllers_ApprovalController', self::defaultRegistrationDenialMailContent);
                $settingsManager->delete('auth.registrationDenialMailContent');   
            } else {
                $settingsManager->set('auth.registrationDenialMailContent', $this->registrationDenialMailContent);
            }
        }

        DynamicConfig::rewrite();
        return true;
    }

}
