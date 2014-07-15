<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationSettingsForm extends CFormModel {

    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;
    public $defaultUserGroup;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('internalUsersCanInvite, internalAllowAnonymousRegistration, internalRequireApprovalAfterRegistration', 'safe'),
            array('defaultUserGroup', 'exist', 'attributeName' => 'id', 'className' => 'Group', 'allowEmpty' => true),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Anonymous users can register'),
            'internalUsersCanInvite' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Members can invite external users by email'),
            'defaultUserGroup' => Yii::t('AdminModule.forms_AuthenticationSettingsForm', 'Default user group for new users'),
        );
    }

}
