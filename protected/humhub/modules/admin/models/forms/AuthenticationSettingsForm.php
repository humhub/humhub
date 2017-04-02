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

    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;
    public $defaultUserGroup;
    public $MinUserNameLength;
    public $MinPasswordLength;
    public $activePasswordSecurity;
    public $defaultUserIdleTimeoutSec;
    public $allowGuestAccess;
    public $defaultUserProfileVisibility;

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
        $this->MinUserNameLength = $settingsManager->get('auth.MinUserNameLength');
        $this->MinPasswordLength = $settingsManager->get('auth.MinPasswordLength');
        $this->activePasswordSecurity = $settingsManager->get('auth.activePasswordSecurity');
        $this->defaultUserIdleTimeoutSec = $settingsManager->get('auth.defaultUserIdleTimeoutSec');
        $this->allowGuestAccess = $settingsManager->get('auth.allowGuestAccess');
        $this->defaultUserProfileVisibility = $settingsManager->get('auth.defaultUserProfileVisibility');
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
            ['MinUserNameLength', 'integer', 'min' => 4],
            ['MinPasswordLength', 'integer', 'min' => 5],
            ['activePasswordSecurity', 'boolean'],
            ['defaultUserIdleTimeoutSec', 'integer', 'min' => 20],
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
            'MinUserNameLength' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Minimum UserName Length'),
            'MinPasswordLength' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Minimum Password Length'),
            'activePasswordSecurity' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Password Complexity Force'),
            'defaultUserIdleTimeoutSec' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user idle timeout, auto-logout (in seconds, optional)'),
            'allowGuestAccess' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Allow limited access for non-authenticated users (guests)'),
            'defaultUserProfileVisibility' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user profile visibility'),
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
        $settingsManager->set('auth.MinUserNameLength', $this->MinUserNameLength);
        $settingsManager->set('auth.MinPasswordLength', $this->MinPasswordLength);
        $settingsManager->set('auth.activePasswordSecurity', $this->activePasswordSecurity);
        $settingsManager->set('auth.defaultUserIdleTimeoutSec', $this->defaultUserIdleTimeoutSec);
        $settingsManager->set('auth.allowGuestAccess', $this->allowGuestAccess);

        if ($settingsManager->get('auth.allowGuestAccess')) {
            $settingsManager->set('auth.defaultUserProfileVisibility', $this->defaultUserProfileVisibility);
        }

        DynamicConfig::rewrite();
        return true;
    }

}
