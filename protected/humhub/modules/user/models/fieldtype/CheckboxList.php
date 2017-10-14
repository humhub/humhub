<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\Profile;
use Yii;

/**
 * CheckboxList profile field for selecting multiple options.
 *
 * @package humhub.modules_core.user.models
 * @since 2.1.1
 */
class CheckboxList extends BaseType
{
    /**
     * All possible options.
     * One entry per line.
     * key=>value format
     *
     * @var string
     */
    public $options;

    /**
     * Allow other selection
     * @var boolean
     */
    public $allowOther;

    /**
     * @var string
     */
    public $other_value;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['options', 'other'], 'safe'],
            [['allowOther'], 'integer'],
        ];
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return Array Form Definition
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            get_class($this) => [
                'type' => 'form',
                'title' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Select field options'),
                'elements' => [
                    'options' => [
                        'type' => 'textarea',
                        'label' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Possible values'),
                        'class' => 'form-control',
                        'hint' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'One option per line. Key=>Value Format (e.g. yes=>Yes)')
                    ],
                    'allowOther' => [
                        'type' => 'checkbox',
                        'label' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Allow other selection'),
                        'class' => 'form-control',
                        'hint' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'This will add an additional input element for custom values')
                    ]
                ]
            ]]);
    }

    /**
     * Saves this Profile Field Type
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName . '_other_selection', 'VARCHAR(255)');
            Yii::$app->db->createCommand($query)->execute();

            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'VARCHAR(255)');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    public function delete()
    {
        // Try create column name
        if (Profile::columnExists($this->profileField->internal_name)) {
            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "_other_selection`;";
            Yii::$app->db->createCommand($sql)->execute();

            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "`;";
            Yii::$app->db->createCommand($sql)->execute();
        }

        return parent::delete();
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition()
    {
        $result = [
            $this->profileField->internal_name => [
                'type' => 'checkboxlist',
                'class' => 'form-control',
                'items' => $this->getSelectItems(),
                'readonly' => (!$this->profileField->editable),
            ]
        ];

        if($this->allowOther) {
            $result[$this->profileField->internal_name. '_other_selection'] = [
                'type' => 'text',
                'class' => 'form-control',
                'label' => false,
                'readonly' => (!$this->profileField->editable),
            ];
        }

        return $result;
    }

    public function beforeProfileSave($values) {
        if(is_array($values)) {
            return implode(',', $values);
        }
        return $values;
    }

    /**
     * Returns a list of possible options
     *
     * @return Array
     */
    public function getSelectItems()
    {
        $items = [];

        foreach (explode("\n", $this->options) as $option) {
            $items[trim($option)] = trim($option);
        }

        if($this->allowOther) {
            $items['other'] = Yii::t($this->profileField->getTranslationCategory(), 'Other:');
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
                return \yii\helpers\Html::encode(Yii::t($this->profileField->getTranslationCategory(), $options[$value]));
            }
        }

        return $value;
    }
}