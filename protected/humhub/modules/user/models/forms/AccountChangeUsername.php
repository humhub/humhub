<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;
use yii\helpers\Url;
use humhub\modules\user\models\User;
use humhub\modules\user\components\CheckPasswordValidator;
use humhub\modules\user\components\UsernameValidator;

/**
 * Form Model for username change
 *
 * @since 0.5
 */
class AccountChangeUsername extends User
{
    const SCENARIO_CHANGE_USERNAME = 'change_username';
    /**
     * @var string the users password
     */
    public $currentPassword;

    /**
     * @var string the users new username
     */
    public $username;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CHANGE_USERNAME] = ['username', 'currentPassword'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules = array_merge($rules, [
            ['username', UsernameValidator::class],
            ['username', 'trim']
        ]);

        if (CheckPasswordValidator::hasPassword()) {
            $rules[] = ['currentPassword', CheckPasswordValidator::class];
            $rules[] = ['currentPassword', 'required'];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => Yii::t('UserModule.forms_AccountChangeUsernameForm', 'Current password'),
            'username' => Yii::t('UserModule.forms_AccountChangeUsernameForm', 'New username'),
        ];
    }
}
