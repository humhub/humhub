<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\Module;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\User;

/**
 * @package humhub.forms
 * @since 0.5
 */
class ApproveUserForm extends \yii\base\Model
{

    public $subject;
    public $message;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            [['subject', 'message'], 'required'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'subject' => Yii::t('AdminModule.user', 'Subject'),
            'message' => Yii::t('AdminModule.user', 'Message'),
        ];
    }

    public function send($email)
    {
        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], ['message' => $this->message]);
        $mail->setTo($email);
        $mail->setSubject($this->subject);
        $mail->send();
    }

    /**
     * Sets the subject and message attribute texts for user approval
     *
     * @param User $user
     * @param User $admin
     */
    public function setApprovalDefaults(User $user, User $admin)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->subject = Yii::t('AdminModule.user',
            "Account Request for '{displayName}' has been approved.",
            ['{displayName}' => Html::encode($user->displayName)]
        );

        if (!empty($module->settings->get('auth.registrationApprovalMailContent'))) {
            $this->message = strtr($module->settings->get('auth.registrationApprovalMailContent'), [
                '{displayName}' => Html::encode($user->displayName),
                '{AdminName}' => Html::encode($admin->displayName),
                '{loginURL}' => urldecode(Url::to(["/user/auth/login"], true)),
            ]);
        } else {
            $this->message = static::getDefaultApprovalMessage(
                Html::encode($user->displayName),
                Html::encode($admin->displayName),
                urldecode(Url::to(["/user/auth/login"], true))
            );
        }
    }

    /**
     * Sets the subject and message attribute texts for user decline
     *
     * @param User $user
     * @param User $admin
     */
    public function setDeclineDefaults(User $user, User $admin)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->subject = Yii::t('AdminModule.user',
            'Account Request for \'{displayName}\' has been declined.',
            ['{displayName}' => Html::encode($user->displayName)]
        );

        if (!empty($module->settings->get('auth.registrationDenialMailContent'))) {
            $this->message = strtr($module->settings->get('auth.registrationDenialMailContent'), [
                '{displayName}' => Html::encode($user->displayName),
                '{AdminName}' => Html::encode($admin->displayName),
            ]);
        } else {
            $this->message = static::getDefaultDeclineMessage(
                Html::encode($user->displayName), Html::encode($admin->displayName)
            );
        }
    }

    /**
     * Returns the default approval message. If not parameters set, the placeholder names are returned.
     *
     * @param string $userDisplayName
     * @param string $adminDisplayName
     * @param string $loginUrl
     * @return string
     */
    public static function getDefaultApprovalMessage($userDisplayName = '{displayName}', $adminDisplayName = '{AdminName}', $loginUrl = '{loginURL}')
    {
        return Yii::t('AdminModule.user', "Hello {displayName},<br><br>\nYour account has been activated.<br><br>\n" .
            "Click here to login:<br>\n<a href='{loginURL}'>{loginURL}</a><br><br>\n\n" .
            "Kind Regards<br>\n{AdminName}<br><br>",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
                '{loginURL}' => $loginUrl,
            ]);
    }

    /**
     * Returns the default decline message. If not parameters set, the placeholder names are returned.
     *
     * @param string $userDisplayName
     * @param string $adminDisplayName
     * @return string
     */
    public static function getDefaultDeclineMessage($userDisplayName = '{displayName}', $adminDisplayName = '{AdminName}')
    {
        return Yii::t('AdminModule.user', "Hello {displayName},<br><br>\n" .
            "Your account request has been declined.<br><br>\n\n" .
            "Kind Regards<br>\n" .
            "{AdminName} <br><br > ",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
            ]);
    }
}
