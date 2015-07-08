<?php

/**
 * ProfileFieldTypeText handles text profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class ProfileFieldTypeText extends ProfileFieldType {

    const VALIDATOR_EMAIL = "email";
    const VALIDATOR_URL = "url";

    /**
     * Minimum Text Length
     *
     * @var Integer
     */
    public $minLength;

    /**
     * Maximum Text Length
     *
     * @var Integer
     */
    public $maxLength = 255;

    /**
     * Validator to use (email, url, none)
     *
     * @var String
     */
    public $validator;

    /**
     * Field Default Text
     *
     * @var String
     */
    public $default;

    /**
     * Regular Expression to check the field
     *
     * @var String
     */
    public $regexp;

    /**
     * Error Message when regular expression fails
     *
     * @var String
     */
    public $regexpErrorMessage;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules() {
        return array(
            array('default, minLength, maxLength, validator, regexp, regexpErrorMessage', 'safe'),
            array('maxLength, minLength', 'numerical', 'min' => 1, 'max' => 255),
            array('default', 'length', 'max' => 255),
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
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Text Field Options'),
                        'elements' => array(
                            'maxLength' => array(
                                'type' => 'text',
                                'maxlength' => 32,
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Maximum length'),
                                'class' => 'form-control',
                            ),
                            'validator' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Validator'),
                                'type' => 'dropdownlist',
                                'class' => 'form-control',
                                'items' => array(
                                    '' => 'None',
                                    self::VALIDATOR_EMAIL => 'E-Mail Address',
                                    self::VALIDATOR_URL => 'URL',
                                ),
                            ),
                            'minLength' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Minimum length'),
                                'type' => 'text',
                                'class' => 'form-control',
                            ),
                            'maxLength' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Maximum length'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ),
                            'default' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Default value'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ),
                            'regexp' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Regular Expression: Validator'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ),
                            'regexpErrorMessage' => array(
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Regular Expression: Error message'),
                                'class' => 'form-control',
                                'type' => 'text',
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
            $sql = "ALTER TABLE profile ADD `" . $columnName . "` VARCHAR(255);";
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

        if ($this->validator == self::VALIDATOR_EMAIL) {
            $rules[] = array($this->profileField->internal_name, 'email');
        } elseif ($this->validator == self::VALIDATOR_URL) {
            $rules[] = array($this->profileField->internal_name, 'url');
        }

        if ($this->maxLength == "" || $this->maxLength > 255) {
            $rules[] = array($this->profileField->internal_name, 'length', 'max' => 255);
        } else {
            $rules[] = array($this->profileField->internal_name, 'length', 'max' => $this->maxLength);
        }

        if ($this->minLength != "") {
            $rules[] = array($this->profileField->internal_name, 'length', 'min' => $this->minLength);
        }

        if ($this->regexp != "") {
            $errorMsg = $this->regexpErrorMessage;
            if ($errorMsg == "") {
                $errorMsg = "Invalid!";
            }
            $rules[] = array($this->profileField->internal_name, 'match', 'pattern' => $this->regexp, 'message' => $errorMsg);
        }

        return parent::getFieldRules($rules);
    }

    public function getUserValue($user, $raw = true) {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName;

        if (!$raw && $this->validator == self::VALIDATOR_EMAIL) {
            return HHtml::link($value, 'mailto:'.$value);
        } elseif (!$raw && $this->validator == self::VALIDATOR_URL) {
            return HHtml::link($value, $value, array('target'=> '_blank'));
        }

        return $value;
    }

}

?>
