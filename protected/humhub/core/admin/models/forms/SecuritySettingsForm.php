<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class SecuritySettingsForm extends CFormModel {

    public $canAdminAlwaysDeleteContent;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('canAdminAlwaysDeleteContent', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'canAdminAlwaysDeleteContent' => Yii::t('AdminModule.forms_SecuritySettingsForm', 'Super Admins can delete each content object'),
        );
    }


}