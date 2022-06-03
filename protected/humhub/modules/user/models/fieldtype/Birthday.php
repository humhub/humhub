<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;

/**
 * ProfileFieldTypeBirthday is a special profile fields for birthdays.
 * It provides an extra option to hide the year on profile
 *
 * @since 0.5
 */
class Birthday extends BaseType
{

    /**
     * The public property $defaultHideAge is configured by loadFieldConfig in BaseType and looks like an integer
     * but is stored as string. The value for $hideAge (the user input) looks like an integer and is stored as integer.
     */

    const DEFAULT_HIDE_AGE_YES = '1';
    const DEFAULT_HIDE_AGE_NO = '0';

    const HIDE_AGE_YES = 1;
    const HIDE_AGE_NO = 0;

    /**
     * @var string hide age by default
     */
    public $defaultHideAge = self::DEFAULT_HIDE_AGE_NO;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['defaultHideAge'], 'in', 'range' => [self::DEFAULT_HIDE_AGE_NO, self::DEFAULT_HIDE_AGE_YES]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            get_class($this) => [
                'type' => 'form',
                'title' => Yii::t('UserModule.profile', 'Birthday field options'),
                'elements' => [
                    'defaultHideAge' => [
                        'type' => 'checkbox',
                        'label' => Yii::t('UserModule.profile', 'Hide age per default'),
                        'class' => 'form-control',
                    ],
                ]
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        // Delete the birthdate_hide_year field
        $columnNameHideYear = $this->profileField->internal_name . '_hide_year';
        if (Profile::columnExists($columnNameHideYear)) {
            $query = Yii::$app->db->getQueryBuilder()->dropColumn(Profile::tableName(), $columnNameHideYear);
            Yii::$app->db->createCommand($query)->execute();
        }

        // Delete the birthdate field (this is done by parent implementation)
        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        // Add the birthdate_hide_year field
        $columnNameHide = $this->profileField->internal_name . '_hide_year';
        if (!Profile::columnExists($columnNameHide)) {
            $query = Yii::$app->db->getQueryBuilder()->addColumn(Profile::tableName(), $columnNameHide, 'INT(1)');
            Yii::$app->db->createCommand($query)->execute();
        }

        // Add the birthdate field
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
        // Add validation for birthdate
        $rules[] = [
            $this->profileField->internal_name,
            \humhub\libs\DbDateValidator::class,
            'format' => 'medium',
            'convertToFormat' => 'Y-m-d',
            'max' => time(),
            'tooBig' => Yii::t('base', 'The date has to be in the past.')
        ];

        // Add validation for birthdate_hide_year
        $rules[] = [
            $this->profileField->internal_name . '_hide_year',
            'in',
            'range' => [self::HIDE_AGE_NO, self::HIDE_AGE_YES]
        ];

        return parent::getFieldRules($rules);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null)
    {
        return [
            $this->profileField->internal_name => [
                'type' => 'datetime',
                'format' => 'medium',
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable),
                'yearRange' => (date('Y') - 100) . ':' . date('Y'),
                'dateTimePickerOptions' => [
                    'pickTime' => false
                ]
            ],
            $this->profileField->internal_name . '_hide_year' => [
                'type' => 'checkbox',
                'readonly' => (!$this->profileField->editable)
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getLabels()
    {
        return [
            $this->profileField->internal_name => Yii::t(
                $this->profileField->getTranslationCategory(),
                $this->profileField->title
            ),
            $this->profileField->internal_name . '_hide_year' => Yii::t(
                $this->profileField->getTranslationCategory(),
                'Hide year in profile'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        $internalName = $this->profileField->internal_name;
        $birthdayDate = \DateTime::createFromFormat('Y-m-d', $user->profile->$internalName,
            new \DateTimeZone(Yii::$app->formatter->timeZone));

        $internalNameHideAge = $this->profileField->internal_name . '_hide_year';
        $hideAge = $user->profile->$internalNameHideAge;

        /*
         * the current value is invalid or empty
         */
        if ($birthdayDate === false) {
            return '';
        }

        /*
         * when getUserValue is called but loadDefaults not $hideAge might be null
         */
        if ($hideAge === null) {
            if ($this->defaultHideAge === self::DEFAULT_HIDE_AGE_YES) {
                $hideAge = self::HIDE_AGE_YES;
            } else {
                $hideAge = self::HIDE_AGE_NO;
            }
        }

        $longDate = Yii::$app->formatter->asDate($birthdayDate, 'long');

        /*
         * - user set hide age yes
         */
        if ($hideAge === self::HIDE_AGE_YES) {
            // See: https://github.com/humhub/humhub/issues/5187#issuecomment-888178022
            
            $month = Yii::$app->formatter->asDate($birthdayDate, 'php:F');
            $day = Yii::$app->formatter->asDate($birthdayDate, 'php:d');
            if (preg_match('/(' . preg_quote($day) . '.+' . preg_quote($month) . '|' . preg_quote($month) . '.+' . preg_quote($day) . ')/', $longDate, $m)) {
                return $m[0];
            }

            $year = Yii::$app->formatter->asDate($birthdayDate, 'php:Y');
            return preg_replace('/[,\s]*' . preg_quote($year) . '([^\d]+|$)/', '', $longDate);
        }

        $ageInYears = Yii::t(
            'UserModule.profile',
            '%y Years',
            ['%y' => $birthdayDate->diff(new \DateTime())->y]
        );

        return $longDate . ' (' . $ageInYears . ')';
    }

    /**
     * @inheritdoc
     */
    public function loadDefaults(Profile $profile)
    {
        //you may configure the default for hideAge as administrator. currently the global default is 0.
        $internalNameHideAge = $this->profileField->internal_name . '_hide_year';
        if ($profile->$internalNameHideAge === null) {
            $profile->$internalNameHideAge = $this->defaultHideAge;
        }
    }

}
