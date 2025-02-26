<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use DateTimeZone;
use humhub\libs\DbDateValidator;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Html;

/**
 * ProfileFieldTypeDateTime
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class DateTime extends BaseType
{
    /**
     * @inheritdoc
     */
    public $type = 'datetime';

    /**
     * Checkbox show also time picker
     *
     * @var bool
     */
    public $showTimePicker = false;

    /**
     * Rules for validating the Field Type Settings Form
     *
     * @return type
     */
    public function rules()
    {
        return [
            [['showTimePicker'], 'in', 'range' => [0, 1]],
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
                'title' => Yii::t('UserModule.profile', 'Date(-time) field options'),
                'elements' => [
                    'showTimePicker' => [
                        'type' => 'checkbox',
                        'label' => Yii::t('UserModule.profile', 'Show date/time picker'),
                        'class' => 'form-control',
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
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName, 'DATETIME');
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
    public function getFieldRules($rules = [])
    {
        $rules[] = [$this->profileField->internal_name, DbDateValidator::class, 'format' => Yii::$app->formatter->dateInputFormat];
        return parent::getFieldRules($rules);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null, array $options = []): array
    {
        return parent::getFieldFormDefinition($user, array_merge([
            'format' => Yii::$app->formatter->dateInputFormat,
            'dateTimePickerOptions' => [
                'pickTime' => ($this->showTimePicker),
            ],
        ], $options));
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, bool $raw = true, bool $encode = true): ?string
    {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName ?? '';
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $value,
            new DateTimeZone(Yii::$app->formatter->timeZone),
        );

        if ($date === false) {
            return '';
        }

        if (!$raw) {
            $value = Yii::$app->formatter->asDatetime($date, 'long');
        }

        return $encode ? Html::encode($value) : $value;
    }

}
