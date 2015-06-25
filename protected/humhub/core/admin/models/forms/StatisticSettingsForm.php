<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class StatisticSettingsForm extends CFormModel {

    public $trackingHtmlCode;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('trackingHtmlCode', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'trackingHtmlCode' => Yii::t('AdminModule.forms_StatisticSettingsForm', 'HTML tracking code'),
        );
    }

}