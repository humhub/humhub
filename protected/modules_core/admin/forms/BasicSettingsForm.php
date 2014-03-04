<?php

/**
 * @package humhub.modules_core.admin.forms 
 * @since 0.5
 */
class BasicSettingsForm extends CFormModel {

    public $name;
    public $baseUrl;
    public $defaultLanguage;
    public $defaultSpaceGuid;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('name, baseUrl', 'required'),
            array('name', 'length', 'max' => 150),
            array('defaultLanguage', 'in', 'range' => Yii::app()->getLanguages()),
            array('defaultSpaceGuid', 'checkSpaceGuid'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'name' => Yii::t('AdminModule.setting', 'Name of the application'),
            'baseUrl' => Yii::t('AdminModule.setting', 'Base URL'),
            'defaultLanguage' => Yii::t('AdminModule.setting', 'Default language'),
            'defaultSpaceGuid' => Yii::t('AdminModule.setting', 'Default space'),
        );
    }

    /**
     * This validator function checks the defaultSpaceGuid.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkSpaceGuid($attribute, $params) {

        if ($this->defaultSpaceGuid != "") {

            foreach (explode(',', $this->defaultSpaceGuid) as $spaceGuid) {
                if ($spaceGuid != "") {
                    $space = Space::model()->findByAttributes(array('guid' => $spaceGuid));
                    if ($space == null) {
                        $this->addError($attribute, Yii::t('AdminModule.setting', "Invalid space"));
                    }
                }
            }
        }
    }

}
