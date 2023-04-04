<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\DbDateValidator;
use humhub\modules\user\models\User;
use Yii;

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
        if (!\humhub\modules\user\models\Profile::columnExists($columnName)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(\humhub\modules\user\models\Profile::tableName(), $columnName, 'DATE');
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
                'pickTime' => false
            ]
        ], $options));
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        $internalName = $this->profileField->internal_name;
        $date = \DateTime::createFromFormat('Y-m-d', $user->profile->$internalName ?? '',
            new \DateTimeZone(Yii::$app->formatter->timeZone));

        if ($date === false)
            return "";

        return $raw ? \yii\helpers\Html::encode($user->profile->$internalName) : Yii::$app->formatter->asDate($date, 'long');
    }

}
