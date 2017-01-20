<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;

/**
 * ProfileFieldTypeCheckbox handles numeric profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Checkbox extends BaseType
{

    /**
     * Field Default Checkbox
     *
     * @var Integer
     */
    public $default = 0;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules()
    {
        return [
            [['default'], 'in', 'range' => [0, 1]]
        ];
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return Array Form Definition
     */
    public function getFormDefinition($definition = array())
    {
        return parent::getFormDefinition([
            get_class($this) => [
                'type' => 'form',
                'title' => Yii::t('UserModule.models_ProfileFieldTypeCheckbox', 'Checkbox field options'),
                'elements' => [
                    'default' => [
                        'label' => Yii::t('UserModule.models_ProfileFieldTypeCheckbox', 'Default value'),
                        'class' => 'form-control',
                        'type' => 'dropdownlist',
                        'items' => [
                            0 => 'Unchecked',
                            1 => 'Checked'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'INT(1) DEFAULT '.$this->default);
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
    public function getFieldRules($rules = array())
    {
        $rules[] = [$this->profileField->internal_name, 'in', 'range' => [0, 1]];
        return parent::getFieldRules($rules);
    }


    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => [
            'type' => 'checkbox',
            'class' => 'form-control',
        ]);
    }

    public function getLabels()
    {
        $labels = array();
        $labels[$this->profileField->internal_name] = Yii::t($this->profileField->getTranslationCategory(), $this->profileField->title);
        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        return $user->profile->$internalName;
    }
}

?>
