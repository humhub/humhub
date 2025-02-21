<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Html;

/**
 * CheckboxList profile field for selecting multiple options.
 *
 * @since 2.1.1
 */
class CheckboxList extends BaseType
{
    /**
     * @inheritdoc
     */
    public $type = 'checkboxlist';

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
     * @var bool
     */
    public $allowOther;

    /**
     * @var string
     */
    public $other;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['options'], 'validateListOptions'],
            [['other'], 'safe'],
            [['allowOther'], 'integer'],
        ];
    }

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return array Form Definition
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            get_class($this) => [
                'type' => 'form',
                'title' => Yii::t('UserModule.profile', 'Select field options'),
                'elements' => [
                    'options' => [
                        'type' => 'textarea',
                        'label' => Yii::t('UserModule.profile', 'Possible values'),
                        'class' => 'form-control autosize',
                        'hint' => Yii::t('UserModule.profile', 'One option per line. Key=>Value Format (e.g. yes=>Yes)'),
                    ],
                    'allowOther' => [
                        'type' => 'checkbox',
                        'label' => Yii::t('UserModule.profile', 'Allow other selection'),
                        'class' => 'form-control',
                        'hint' => Yii::t('UserModule.profile', 'This will add an additional input element for custom values'),
                    ],
                ],
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

            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName, 'VARCHAR(255)');
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

        parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null, array $options = []): array
    {
        $result = parent::getFieldFormDefinition($user, array_merge([
            'delimiter' => "\n",
            'items' => $this->getSelectItems(),
        ], $options));

        if ($this->allowOther) {
            $result[$this->profileField->internal_name . '_other_selection'] = [
                'type' => 'text',
                'class' => 'form-control',
                'label' => false,
                'readonly' => (!$this->profileField->editable),
            ];
        }

        return $result;
    }

    public function beforeProfileSave($values)
    {
        if (is_array($values)) {
            return implode("\n", $values);
        }
        return $values;
    }

    /**
     * @inheritdoc
     */
    public function getSelectItems(): array
    {
        $items = parent::getSelectItems();

        if ($this->allowOther) {
            $items['other'] = Yii::t('UserModule.profile', 'Other:');
        }

        return $items;
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, bool $raw = true, bool $encode = true): ?string
    {
        $internalName = $this->profileField->internal_name;
        $internalNameOther = $internalName . '_other_selection';

        $value = $user->profile->$internalName;
        if (!$raw && $value !== null) {
            $options = $this->getSelectItems();
            $translatedValues = [];
            if (is_string($value)) {
                $value = explode("\n", $value);
            }
            foreach ($value as $v) {
                if ($v === 'other' && !empty($user->profile->$internalNameOther)) {
                    $translatedValues[] = $user->profile->$internalNameOther;
                } elseif (isset($options[$v])) {
                    $translatedValues[] = Yii::t($this->profileField->getTranslationCategory(), $options[$v]);
                }
            }
            $value = implode(', ', $translatedValues);
        }

        return $encode ? Html::encode($value) : $value;
    }
}
