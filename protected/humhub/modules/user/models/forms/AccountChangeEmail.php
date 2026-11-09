<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use humhub\modules\user\models\User;

/**
 * Form Model for email change
 *
 * @since 0.5
 */
class AccountChangeEmail extends Model
{
    /**
     * @var string the users new email address
     */
    public $newEmail;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['newEmail', 'required'],
            ['newEmail', 'string', 'max' => 150],
            ['newEmail', 'email'],
            ['newEmail', 'unique', 'targetAttribute' => 'email', 'targetClass' => User::class, 'message' => '{attribute} "{value}" is already in use!'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'newEmail' => Yii::t('UserModule.account', 'New E-Mail address'),
        ];
    }

    /**
     * Sends Change E-Mail E-Mail
     */
    public function sendChangeEmail($approveUrl = '')
    {
        $user = Yii::$app->user->getIdentity();

        $token = md5(Yii::$app->settings->get('secret') . $user->guid . $this->newEmail);

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/ChangeEmail',
            'text' => '@humhub/modules/user/views/mails/plaintext/ChangeEmail',
        ], [
            'user' => $user,
            'newEmail' => $this->newEmail,
            'approveUrl' => Url::to([empty($approveUrl) ? "/user/account/change-email-validate" : $approveUrl, 'email' => $this->newEmail, 'token' => $token], true),
        ]);
        $mail->setTo($this->newEmail);
        $mail->setSubject(Yii::t('UserModule.account', 'E-Mail change'));
        $mail->send();

        return true;
    }

}
