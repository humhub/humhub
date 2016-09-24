<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use Yii;

/**
 * ProfileFieldTypeNumber handles numeric profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class Number extends BaseType
{

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
    public function rules()
    {
        return array(
            array(['maxValue', 'minValue'], 'integer', 'min' => 0),
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
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'INT');
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

        $rules[] = array($this->profileField->internal_name, 'integer');

        if ($this->maxValue) {
            $rules[] = array($this->profileField->internal_name, 'integer', 'max' => $this->maxValue);
        }

        if ($this->minValue) {
            $rules[] = array($this->profileField->internal_name, 'integer', 'min' => $this->minValue);
        }

        return parent::getFieldRules($rules);
    }

}

?>
