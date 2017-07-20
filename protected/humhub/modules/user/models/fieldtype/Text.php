<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;

/**
 * ProfileFieldTypeText handles text profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Text extends BaseType
{

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
    public function rules()
    {
        return array(
            array(['default', 'minLength', 'maxLength', 'validator', 'regexp', 'regexpErrorMessage'], 'safe'),
            array(['maxLength', 'minLength'], 'integer', 'min' => 1, 'max' => 255),
            array(['default'], 'string', 'max' => 255),
        );
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @param array $definition
     * @return Array Form Definition
     */
    public function getFormDefinition($definition = array())
    {
        return parent::getFormDefinition([
                    get_class($this) => [
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Text Field Options'),
                        'elements' => [
                            'maxLength' => [
                                'type' => 'text',
                                'maxlength' => 32,
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Maximum length'),
                                'class' => 'form-control',
                            ],
                            'validator' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Validator'),
                                'type' => 'dropdownlist',
                                'class' => 'form-control',
                                'items' => [
                                    '' => 'None',
                                    self::VALIDATOR_EMAIL => 'E-Mail Address',
                                    self::VALIDATOR_URL => 'URL',
                                ],
                            ],
                            'minLength' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Minimum length'),
                                'type' => 'text',
                                'class' => 'form-control',
                            ],
                            'maxLength' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Maximum length'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ],
                            'default' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Default value'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ],
                            'regexp' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Regular Expression: Validator'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ],
                            'regexpErrorMessage' => [
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeText', 'Regular Expression: Error message'),
                                'class' => 'form-control',
                                'type' => 'text',
                            ],
                        ]
                    ]]);
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'VARCHAR(255)');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    /**
     * Returns the Field Rules, to validate users input
     *
     * @param type $rules
     * @return type
     */
    public function getFieldRules($rules = [])
    {

        if ($this->validator == self::VALIDATOR_EMAIL) {
            $rules[] = [$this->profileField->internal_name, 'email'];
        } elseif ($this->validator == self::VALIDATOR_URL) {
            $rules[] = [$this->profileField->internal_name, 'url'];
        }

        if ($this->maxLength == "" || $this->maxLength > 255) {
            $rules[] = [$this->profileField->internal_name, 'string', 'max' => 255];
        } else {
            $rules[] = [$this->profileField->internal_name, 'string', 'max' => $this->maxLength];
        }

        if ($this->minLength != "") {
            $rules[] = [$this->profileField->internal_name, 'string', 'min' => $this->minLength];
        }

        if ($this->regexp != "") {
            $errorMsg = $this->regexpErrorMessage;
            if ($errorMsg == "") {
                $errorMsg = "Invalid!";
            }
            $rules[] = [$this->profileField->internal_name, 'match', 'pattern' => $this->regexp, 'message' => $errorMsg];
        }

        return parent::getFieldRules($rules);
    }

    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName;

        if (!$raw && $this->validator == self::VALIDATOR_EMAIL) {
            return \yii\helpers\Html::a(\yii\helpers\Html::encode($value), 'mailto:' . $value);
        } elseif (!$raw && $this->validator == self::VALIDATOR_URL) {
            return \yii\helpers\Html::a(\yii\helpers\Html::encode($value), $value, array('target' => '_blank'));
        }

        return \yii\helpers\Html::encode($value);
    }

}

?>
