<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
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
 * Date Field
 *
 * @since 1.0.0-beta.4
 */
class Date extends BaseType
{
    /**
     * @inheritdoc
     */
    public $type = 'datetime';

    /**
     * @inheritdoc
     */
    public function save()
    {
        $columnName = $this->profileField->internal_name;
        if (!Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnName, 'DATE');
            Yii::$app->db->createCommand($query)->execute();
        }

        return parent::save();
    }

    /**
     * @inheritdoc
     */
    public function getFieldRules($rules = [])
    {
        $rules[] = [
            $this->profileField->internal_name,
            DbDateValidator::class,
            'format' => Yii::$app->formatter->dateInputFormat,
            'convertToFormat' => 'Y-m-d',
        ];
        return parent::getFieldRules($rules);
    }

    /**
     * @inheritdoc
     */
    public function getFormDefinition($definition = [])
    {
        return count($definition) > 0 ? parent::getFormDefinition($definition) : [];
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null, array $options = []): array
    {
        return parent::getFieldFormDefinition($user, array_merge([
            'format' => Yii::$app->formatter->dateInputFormat,
            'dateTimePickerOptions' => [
                'pickTime' => false,
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
            'Y-m-d',
            $value,
            new DateTimeZone(Yii::$app->formatter->timeZone),
        );

        if ($date === false) {
            return '';
        }

        if (!$raw) {
            $value = Yii::$app->formatter->asDate($date, 'long');
        }

        return $encode ? Html::encode($value) : $value;
    }

}
