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

        $this->subject = Yii::t('AdminModule.user', "Account Request for '{displayName}' has been approved.",
            ['{displayName}' => Html::encode($user->displayName)]
        );

        $this->message = strtr($module->settings->get('auth.registrationApprovalMailContent', Yii::t('AdminModule.user', AuthenticationSettingsForm::defaultRegistrationApprovalMailContent)), [
            '{displayName}' => Html::encode($user->displayName),
            '{AdminName}' => Html::encode($admin->displayName),
            '{loginURL}' => urldecode(Url::to(["/user/auth/login"], true)),
        ]);

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

        $this->subject = Yii::t('AdminModule.user', 'Account Request for \'{displayName}\' has been declined.',
            ['{displayName}' => Html::encode($user->displayName)]
        );
        $this->message = strtr($module->settings->get('auth.registrationDenialMailContent', Yii::t('AdminModule.user', AuthenticationSettingsForm::defaultRegistrationDenialMailContent)), [
            '{displayName}' => Html::encode($user->displayName),
            '{AdminName}' => Html::encode($admin->displayName),
        ]);
    }

}
