<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationSettingsForm extends CFormModel
{

    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;
    public $defaultUserGroup;
    public $defaultUserIdleTimeoutSec;
    public $allowGuestAccess;
    public $defaultUserProfileVisibility;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('internalUsersCanInvite, internalAllowAnonymousRegistration, internalRequireApprovalAfterRegistration, allowGuestAccess', 'safe'),
            array('defaultUserGroup', 'exist', 'attributeName' => 'id', 'className' => 'Group', 'allowEmpty' => true),
            array('defaultUserProfileVisibility', 'numerical'),
            array('defaultUserIdleTimeoutSec', 'numerical', 'integerOnly' => true, 'min' => 20),
            array('defaultUserIdleTimeoutSec', 'length', 'max' => 10)
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Anonymous users can register'),
            'internalUsersCanInvite' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Members can invite external users by email'),
            'defaultUserGroup' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user group for new users'),
            'defaultUserIdleTimeoutSec' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user idle timeout, auto-logout (in seconds, optional)'),
            'allowGuestAccess' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Allow limited access for non-authenticated users (guests)'),
            'defaultUserProfileVisibility' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user profile visibility'),
        );
    }

}
