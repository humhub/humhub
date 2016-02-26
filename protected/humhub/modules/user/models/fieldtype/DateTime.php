<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;
use humhub\libs\DbDateValidator;

/**
 * ProfileFieldTypeDateTime
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class DateTime extends BaseType
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
            array(['showTimePicker'], 'in', 'range' => array(0, 1))
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
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'DATETIME');
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
        $rules[] = array($this->profileField->internal_name, DbDateValidator::className(), 'format' => Yii::$app->formatter->dateInputFormat);
        return parent::getFieldRules($rules);
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        return array($this->profileField->internal_name => array(
                'type' => 'datetime',
                'format' => Yii::$app->formatter->dateInputFormat,
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable),
                'dateTimePickerOptions' => array(
                    'pickTime' => ($this->showTimePicker)
                )
        ));
    }

    /**
     * @inheritdoc
     */
    public function getUserValue($user, $raw = true)
    {

        $internalName = $this->profileField->internal_name;
        $date = $user->profile->$internalName;

        if ($date == "" || $date == "0000-00-00 00:00:00")
            return "";

        return \yii\helpers\Html::encode($date);
    }

}

?>
