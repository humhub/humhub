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

namespace humhub\core\user\models\fieldtype;

use Yii;

/**
 * ProfileFieldTypeBirthday is a special profile fields for birthdays.
 * It provides an extra option to hide the year on profile
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Birthday extends DateTime
{

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
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeBirthday', 'Birthday field options'),
                        'elements' => array(
                        )
        )));
    }

    public function delete()
    {
        // Try create column name
        if (Profile::model()->columnExists($this->profileField->internal_name)) {
            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "_hide_year`;";
            $this->profileField->dbConnection->createCommand($sql)->execute();
        }

        return parent::delete();
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\core\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\core\user\models\Profile::tableName(), $columnName, 'INT(1)');
            Yii::$app->db->createCommand($query)->execute();
        } else {
            Yii::error('Could not add profile column - already exists!');
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

        $rules[] = array($this->profileField->internal_name . "_hide_year", 'in', 'range' => array(0, 1));
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
            ),
            $this->profileField->internal_name . "_hide_year" => array(
                'type' => 'checkbox',
            ),
        );
    }

    public function getLabels()
    {
        $labels = array();
        $labels[$this->profileField->internal_name] = Yii::t($this->profileField->getTranslationCategory(), $this->profileField->title);
        $labels[$this->profileField->internal_name . "_hide_year"] = Yii::t($this->profileField->getTranslationCategory(), "Hide year in profile");
        return $labels;
    }

    public function getUserValue($user, $raw = true)
    {

        $internalName = $this->profileField->internal_name;
        $birthdayDate = $user->profile->$internalName;

        if ($birthdayDate == "" || $birthdayDate == "0000-00-00 00:00:00")
            return "";

        $internalNameHideAge = $this->profileField->internal_name . "_hide_year";
        $hideAge = $user->profile->$internalNameHideAge;

        if (!$hideAge) {

            $birthDate = new \DateTime($birthdayDate);
            $lifeSpan = $birthDate->diff(new \DateTime());
            $age = Yii::t('UserModule.models_ProfileFieldTypeBirthday', '%y Years', array('%y' => $lifeSpan->format("%y")));

            return Yii::$app->formatter->asDate($birthdayDate, 'long') . " (" . $age . ")";
        } else {
            return Yii::$app->formatter->asDate($birthdayDate, 'MMMM yyyy');
        }
    }

}

?>
