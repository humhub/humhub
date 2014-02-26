<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationSettingsForm extends CFormModel {

    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;



    /**
     * Declares the validation rules.
     */
    public function rules() {

        //$themes = HTheme::getThemes();

        return array(
            //array('theme', 'in', 'range'=>$themes),
            array('internalUsersCanInvite, internalAllowAnonymousRegistration, internalRequireApprovalAfterRegistration', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModuleauthentication', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.authentication', 'Anonymous users can register'),
            'internalUsersCanInvite' => Yii::t('AdminModule.authentication', 'Members can invite external users by email')
        );
    }

}