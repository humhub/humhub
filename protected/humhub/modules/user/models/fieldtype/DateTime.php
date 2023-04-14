<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\DbDateValidator;
use humhub\modules\user\models\User;
use Yii;

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
        return [
            [['showTimePicker'], 'in', 'range' => [0, 1]]
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
                        ]
        ]]);
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
                'pickTime' => ($this->showTimePicker)
            ]
        ], $options));
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        $internalName = $this->profileField->internal_name;

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $user->profile->$internalName ?? '',
            new \DateTimeZone(Yii::$app->formatter->timeZone));

        if ($date === false)
            return "";

        return $raw ? \yii\helpers\Html::encode($user->profile->$internalName) : Yii::$app->formatter->asDatetime($date, 'long');
    }

}

?>
