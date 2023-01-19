<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\user\Module;
use Yii;
use humhub\modules\user\models\User;
use humhub\modules\user\components\CheckPasswordValidator;

/**
 * Form Model for username change
 *
 * @since 1.4
 */
class AccountChangeUsername extends \yii\base\Model
{

    /**
     * @var string the users password
     */
    public $currentPassword;

    /**
     * @var string the users new email address
     */
    public $newUsername;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        $rules = [
            ['newUsername', 'required'],
            ['newUsername', 'string', 'min' => $userModule->minimumUsernameLength, 'max' => $userModule->maximumUsernameLength],
            ['newUsername', 'unique', 'targetAttribute' => 'username', 'targetClass' => User::class, 'message' => '{attribute} "{value}" is already in use!'],
            ['newUsername', 'match', 'pattern' => $userModule->validUsernameRegexp, 'message' => Yii::t('UserModule.base', 'Username contains invalid characters.'), 'enableClientValidation' => false],
            ['newUsername', 'trim'],
            [['newUsername'], 'validateForbiddenUsername'],
        ];

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
            'currentPassword' => Yii::t('UserModule.account', 'Current password'),
            'newUsername' => Yii::t('UserModule.account', 'New User name'),
        ];
    }

    /**
     * Sends Change Username E-Mail
     */
    public function sendChangeUsername()
    {
        $user = Yii::$app->user->getIdentity();
        $user->username = $this->newUsername;
        $user->save();

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/ChangeUsername',
            'text' => '@humhub/modules/user/views/mails/plaintext/ChangeUsername'
        ], [
            'user' => $user,
            'newUsername' => $this->newUsername,
        ]);
        $mail->setTo($user->email);
        $mail->setSubject(Yii::t('UserModule.account', 'Username has been changed'));
        $mail->send();

        return true;
    }

    /**
     * Validate attribute newUsername
     * @param string $attribute
     */
    public function validateForbiddenUsername($attribute, $params)
    {
        if (in_array(strtolower($this->$attribute), Yii::$app->controller->module->forbiddenUsernames)){
            $this->addError($attribute, Yii::t('UserModule.account', 'You cannot use this username.'));
        }
    }

}
