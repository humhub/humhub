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
            'name' => Yii::t('AdminModule.base', 'Name of the application'),
            'baseUrl' => Yii::t('AdminModule.base', 'Base URL'),
            'defaultLanguage' => Yii::t('AdminModule.base', 'Default language'),
            'defaultSpaceGuid' => Yii::t('AdminModule.base', 'Default space'),
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

            $this->defaultSpaceGuid = rtrim($this->defaultSpaceGuid, ',');
            $this->defaultSpaceGuid = trim($this->defaultSpaceGuid);

            $space = Space::model()->findByAttributes(array('guid' => $this->defaultSpaceGuid));

            if ($space != null) {
                $this->defaultSpaceGuid = $space->guid;
            } else {
                $this->addError($attribute, Yii::t('AdminModule.base', "Invalid space"));
            }
        }
    }

}