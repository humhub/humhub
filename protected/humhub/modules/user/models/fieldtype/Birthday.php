<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\Profile;
use Yii;

/**
 * ProfileFieldTypeBirthday is a special profile fields for birthdays.
 * It provides an extra option to hide the year on profile
 *
 * @since 0.5
 */
class Birthday extends Date
{

    /**
     * @var boolean hide age per default
     */
    public $defaultHideAge = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['defaultHideAge'], 'in', 'range' => array(0, 1)]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFormDefinition($definition = array())
    {
        return parent::getFormDefinition([
                    get_class($this) => [
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeBirthday', 'Birthday field options'),
                        'elements' => [
                            'defaultHideAge' => [
                                'type' => 'checkbox',
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeBirthday', 'Hide age per default'),
                                'class' => 'form-control',
                            ],
                        ]
                    ]
        ]);
    }

    public function delete()
    {
        // Try create column name
        if (Profile::columnExists($this->profileField->internal_name)) {
            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "_hide_year`;";
            Yii::$app->db->createCommand($sql)->execute();
        }

        return parent::delete();
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName . '_hide_year', 'INT(1)');
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

        $rules[] = [$this->profileField->internal_name . "_hide_year", 'in', 'range' => [0, 1]];
        $rules[] = [$this->profileField->internal_name,
            \humhub\libs\DbDateValidator::className(),
            'format' => Yii::$app->formatter->dateInputFormat,
            'convertToFormat' => null,
            'max' => time(),
            'tooBig' => Yii::t('base', 'The date has to be in the past.')
        ];
        return parent::getFieldRules($rules);
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        return [$this->profileField->internal_name => [
                'type' => 'datetime',
                'format' => Yii::$app->formatter->dateInputFormat,
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable),
                'yearRange' => (date('Y') - 100) . ":" . date('Y')
            ],
            $this->profileField->internal_name . "_hide_year" => [
                'type' => 'checkbox',
                'readonly' => (!$this->profileField->editable)
            ],
        ];
    }

    public function getLabels()
    {
        $labels = array();
        $labels[$this->profileField->internal_name] = Yii::t($this->profileField->getTranslationCategory(), $this->profileField->title);
        $labels[$this->profileField->internal_name . "_hide_year"] = Yii::t($this->profileField->getTranslationCategory(), "Hide year in profile");
        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        $birthdayDate = $user->profile->$internalName;

        if ($birthdayDate == "" || $birthdayDate == "0000-00-00")
            return "";

        $internalNameHideAge = $this->profileField->internal_name . "_hide_year";

        $hideAge = $user->profile->$internalNameHideAge;
        if (($hideAge === null && !$this->defaultHideAge) || $hideAge === 0) {
            $birthDate = new \DateTime($birthdayDate);
            $lifeSpan = $birthDate->diff(new \DateTime());
            $age = Yii::t('UserModule.models_ProfileFieldTypeBirthday', '%y Years', array('%y' => $lifeSpan->format("%y")));

            return Yii::$app->formatter->asDate($birthdayDate, 'long') . " (" . $age . ")";
        } else {
            return Yii::$app->formatter->asDate($birthdayDate, 'dd. MMMM');
        }
    }

    /**
     * @inheritdoc
     */
    public function loadDefaults(\humhub\modules\user\models\Profile $profile)
    {
        $internalNameHideAge = $this->profileField->internal_name . '_hide_year';
        if ($profile->$internalNameHideAge === null) {
            $profile->$internalNameHideAge = $this->defaultHideAge;
        }
    }

}

?>
