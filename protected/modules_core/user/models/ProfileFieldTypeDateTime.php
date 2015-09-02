<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * ProfileFieldTypeDateTime
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class ProfileFieldTypeDateTime extends ProfileFieldType
{

    /**
     * Checkbox show also time picker
     *
     * @var boolean
     */
    public $showTimePicker = false;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules()
    {
        return array(
            array('showTimePicker', 'in', 'range' => array(0, 1), 'allowEmpty' => true)
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
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeDateTime', 'Date(-time) field options'),
                        'elements' => array(
                            'showTimePicker' => array(
                                'type' => 'checkbox',
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeDateTime', 'Show date/time picker'),
                                'class' => 'form-control',
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
            $sql = "ALTER TABLE profile ADD `" . $columnName . "` DATETIME;";
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
        $rules[] = array($this->profileField->internal_name, 'date', 'format' => 'yyyy-MM-dd hh:mm:ss');
        return parent::getFieldRules($rules);
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => array(
                'type' => 'datetime',
                'class' => 'form-control',
                'dateTimePickerOptions' => array(
                    'pickTime' => ($this->showTimePicker)
                )
        ));
    }

    public function getUserValue($user, $raw = true)
    {

        $internalName = $this->profileField->internal_name;
        $date = $user->profile->$internalName;

        if ($date == "" || $date == "0000-00-00 00:00:00")
            return "";

        return $date;
    }

}

?>
