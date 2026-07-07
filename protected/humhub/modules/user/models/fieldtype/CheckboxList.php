<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\helpers\Html;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;

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
     * @inerhitdoc
     */
    public $canBeDirectoryFilter = true;

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
     * Delimiter used to join/split multi values when stored in the profile column.
     *
     * This is intentionally a class constant, not a configurable property: nothing in
     * the admin UI exposes changing it, there is no migration path for already stored
     * values if it were changed, and other consumers (e.g. {@see \humhub\modules\user\components\PeopleQuery})
     * rely on it being fixed.
     *
     * @since 1.18.4
     */
    public const MULTI_VALUE_DELIMITER = "\n";

    /**
     * Returns the name of the profile column that stores the "Other:" value entered by
     * users for a CheckboxList field with the given internal name.
     */
    public static function getOtherColumnName(string $internalName): string
    {
        return $internalName . '_other_selection';
    }

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
            static::class => [
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
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), self::getOtherColumnName($columnName), 'VARCHAR(255)');
            Yii::$app->db->createCommand($query)->execute();

            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName, 'TEXT');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    public function delete()
    {
        // Try create column name
        if (Profile::columnExists($this->profileField->internal_name)) {
            $sql = "ALTER TABLE profile DROP `" . self::getOtherColumnName($this->profileField->internal_name) . "`;";
            Yii::$app->db->createCommand($sql)->execute();

            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "`;";
            Yii::$app->db->createCommand($sql)->execute();
        }

        parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(?User $user = null, array $options = []): array
    {
        $result = parent::getFieldFormDefinition($user, array_merge([
            'delimiter' => self::MULTI_VALUE_DELIMITER,
            'items' => $this->getSelectItems(),
        ], $options));

        if ($this->allowOther) {
            $result[self::getOtherColumnName($this->profileField->internal_name)] = [
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
            return implode(self::MULTI_VALUE_DELIMITER, $values);
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
        $internalNameOther = self::getOtherColumnName($internalName);

        $value = $user->profile->$internalName;
        if (!$raw && $value !== null) {
            $options = $this->getSelectItems();
            $translatedValues = [];
            if (is_string($value)) {
                $value = explode(self::MULTI_VALUE_DELIMITER, $value);
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
