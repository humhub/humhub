<?php

/**
 * ProfileFieldTypeNumber handles numeric profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class ProfileFieldTypeNumber extends ProfileFieldType {

    /**
     * Maximum Int Value
     *
     * @var type
     */
    public $maxValue;

    /**
     * Minimum Int Value
     *
     * @var type
     */
    public $minValue;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules() {
        return array(
            array('maxValue, minValue', 'numerical', 'min' => 0),
        );
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return Array Form Definition
     */
    public function getFormDefinition($definition = array()) {
        return parent::getFormDefinition(array(
                    get_class($this) => array(
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeNumber', 'Number field options'),
                        'elements' => array(
                            'maxValue' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeNumber', 'Maximum value'),                                
                                'type' => 'text',
                                'class' => 'form-control',
                            ),
                            'minValue' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeNumber', 'Minimum value'),                                
                                'type' => 'text',
                                'class' => 'form-control',
                            ),
                        )
        )));
    }

    /**
     * Saves this Profile Field Type
     */
    public function save() {

        $columnName = $this->profileField->internal_name;

        // Try create column name
        if (!Profile::model()->columnExists($columnName)) {
            $sql = "ALTER TABLE profile ADD `" . $columnName . "` INT;";
            $this->profileField->dbConnection->createCommand($sql)->execute();
        }

        parent::save();
    }

    /**
     * Returns the Field Rules, to validate users input
     *
     * @param type $rules
     * @return type
     */
    public function getFieldRules($rules = array()) {

        $rules[] = array($this->profileField->internal_name, 'numerical');

        if ($this->maxValue) {
            $rules[] = array($this->profileField->internal_name, 'numerical', 'max' => $this->maxValue);
        }

        if ($this->minValue) {
            $rules[] = array($this->profileField->internal_name, 'numerical', 'min' => $this->minValue);
        }

        return parent::getFieldRules($rules);
    }

}

?>
