<?php

/**
 * ProfileFieldTypeSelect handles numeric profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class ProfileFieldTypeSelect extends ProfileFieldType
{

    /**
     * All possible options.
     * One entry per line.
     * key=>value format
     * 
     * @var String
     */
    public $options;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules()
    {
        return array(
            array('options', 'safe'),
        );
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return Array Form Definition
     */
    public function getFormDefinition($definition = array())
    {
        return parent::getFormDefinition(array(
                    get_class($this) => array(
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Select field options'),
                        'elements' => array(
                            'options' => array(
                                'type' => 'textarea',
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Possible values'),
                                'class' => 'form-control',
                                'hint' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'One option per line. Key=>Value Format (e.g. yes=>Yes)')
                            ),
                        )
        )));
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {

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
    public function getFieldRules($rules = array())
    {
        $rules[] = array($this->profileField->internal_name, 'in', 'range' => array_keys($this->getSelectItems()));
        return parent::getFieldRules($rules);
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => array(
                'type' => 'dropdownlist',
                'class' => 'form-control',
                'items' => $this->getSelectItems(),
                'prompt'=> Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Please select:'),
        ));
    }

    /**
     * Returns a list of possible options
     * 
     * @return Array
     */
    public function getSelectItems()
    {
        $items = array();

        foreach (explode("\n", $this->options) as $option) {

            if (strpos($option, "=>") !== false) {
                list($key, $value) = explode("=>", $option);
                $items[trim($key)] = Yii::t($this->profileField->getTranslationCategory(), trim($value));
            } else {
                $items[] = $option;
            }
        }

        return $items;
    }

    /**
     * Returns value of option
     * 
     * @param User $user
     * @param Boolean $raw Output Key
     * @return String
     */
    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName;

        if (!$raw) {
            $options = $this->getSelectItems();
            if (isset($options[$value])) {
                return Yii::t($this->profileField->getTranslationCategory(), $options[$value]);
            }
        }

        return $value;
    }

}

?>
