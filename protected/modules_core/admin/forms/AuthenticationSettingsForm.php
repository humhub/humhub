<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationSettingsForm extends CFormModel {

    public $authInternal;
    public $internalAllowAnonymousRegistration;
    public $internalRequireApprovalAfterRegistration;
    public $internalUsersCanInvite;
    public $authLdap;



    /**
     * Declares the validation rules.
     */
    public function rules() {

        //$themes = HTheme::getThemes();

        return array(
            //array('theme', 'in', 'range'=>$themes),
            array('authInternal, authLdap, internalUsersCanInvite, internalAllowAnonymousRegistration, internalRequireApprovalAfterRegistration', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'authInternal' => Yii::t('AdminModule.base', 'Enable'),
            'authLdap' => Yii::t('AdminModule.base', 'Enable'),
            'internalRequireApprovalAfterRegistration' => Yii::t('AdminModule.base', 'Require group admin approval after registration'),
            'internalAllowAnonymousRegistration' => Yii::t('AdminModule.base', 'Anonymous users can register'),
            'internalUsersCanInvite' => Yii::t('AdminModule.base', 'Members can invite external users by email')
        );
    }

}