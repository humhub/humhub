<?php

namespace humhub\modules\admin\models\forms;

use humhub\components\i18n\I18N;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\Module;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\User;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * @package humhub.forms
 * @since 0.5
 */
class ApproveUserForm extends \yii\base\Model
{

    /**
     * @var User
     */
    public $user;

    /**
     * @var User
     */
    public $admin;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $message;

    public function __construct($userId)
    {
        $this->admin = Yii::$app->user->getIdentity();
        $this->user = $this->getUser($userId);
        parent::__construct([]);
    }

    public function init()
    {
        parent::init();
        if(!($this->user instanceof  User)) {
            throw new NotFoundHttpException(Yii::t('AdminModule.controllers_ApprovalController', 'User not found!'));
        }

        if($this->user->status !== User::STATUS_NEED_APPROVAL) {
            throw new NotFoundHttpException(Yii::t('AdminModule.controllers_ApprovalController', 'Invalid user state: {state}', ['state' => $this->user->status]));
        }

        if(!($this->admin instanceof User)) {
            throw new ForbiddenHttpException();
        }

        if(!$this->admin->canApproveUsers()) {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * @param $id int
     * @return User|null
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    private function getUser($id)
    {
        return User::find()
            ->andWhere(['user.id' => (int) $id, 'user.status' => User::STATUS_NEED_APPROVAL])
            ->administrableBy($this->admin)
            ->one();
    }

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

    /**
     * Approves user by sending approval mail and updating user status and running initial approval logic.
     * @return bool
     */
    public function approve()
    {
        if(!$this->message) {
            $this->setApprovalDefaults();
        }

        if(!$this->validate()) {
            return false;
        }

        $this->send();
        $this->user->status = User::STATUS_ENABLED;
        $this->user->save();
        $this->user->setUpApproved();
        return true;
    }

    /**
     * Declines user by sending denial mail and deleting the user.
     * @return bool
     */
    public function decline()
    {
        if(!$this->message) {
            $this->setDeclineDefaults();
        }

        if(!$this->validate()) {
            return false;
        }

        $this->send();
        $this->user->delete();
        return true;
    }

    public function send()
    {
        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], ['message' => $this->message]);
        $mail->setTo($this->user->email);
        $mail->setSubject($this->subject);
        $mail->send();
    }

    /**
     * Sets the subject and message attribute texts for user approval
     *
     * @param User $user
     * @param User $admin
     */
    public function setApprovalDefaults()
    {
        Yii::$app->i18n->setUserLocale($this->user);

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->subject = Yii::t('AdminModule.user',
            "Account Request for '{displayName}' has been approved.",
            ['{displayName}' => Html::encode($this->user->displayName)]
        );

        $loginLink = Html::a(urldecode(Url::to(["/user/auth/login"], true)), Url::to(["/user/auth/login"], true));
        $loginURL = urldecode(Url::to(["/user/auth/login"], true));
        $userName =  Html::encode($this->user->displayName);
        $adminName =  Html::encode($this->admin->displayName);

        if (!empty($module->settings->get('auth.registrationApprovalMailContent'))) {
            $this->message = Yii::t('AdminModule.user', $module->settings->get('auth.registrationApprovalMailContent'), [
                '{displayName}' => $userName,
                '{AdminName}' => $adminName,
                '{loginLink}' => $loginLink,
                '{loginURL}' => $loginURL
            ]);
        } else {
            $this->message = static::getDefaultApprovalMessage($userName, $adminName, $loginLink);
        }

        Yii::$app->i18n->autosetLocale();
    }

    /**
     * Sets the subject and message attribute texts for user decline
     *
     * @param User $user
     * @param User $admin
     */
    public function setDeclineDefaults()
    {
        Yii::$app->i18n->setUserLocale($this->user);

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->subject = Yii::t('AdminModule.user',
            'Account Request for \'{displayName}\' has been declined.',
            ['{displayName}' => Html::encode($this->user->displayName)]
        );

        if (!empty($module->settings->get('auth.registrationDenialMailContent'))) {
            $this->message = Yii::t('AdminModule.user', $module->settings->get('auth.registrationDenialMailContent'), [
                '{displayName}' => Html::encode($this->user->displayName),
                '{AdminName}' => Html::encode($this->admin->displayName),
            ]);
        } else {
            $this->message = static::getDefaultDeclineMessage(
                Html::encode($this->user->displayName), Html::encode($this->admin->displayName)
            );
        }

        Yii::$app->i18n->autosetLocale();
    }

    /**
     * Returns the default approval message. If not parameters set, the placeholder names are returned.
     *
     * @param string $userDisplayName
     * @param string $adminDisplayName
     * @param string $loginUrl
     * @return string
     */
    public static function getDefaultApprovalMessage($userDisplayName = '{displayName}', $adminDisplayName = '{AdminName}', $loginLink = '{loginLink}')
    {
        return Yii::t('AdminModule.user', "Hello {displayName},<br><br>\nYour account has been activated.<br><br>\n" .
            "Click here to login:<br>\n{loginLink}<br><br>\n\n" .
            "Kind Regards<br>\n{AdminName}<br><br>",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
                '{loginLink}' => $loginLink,
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
            "{AdminName} <br><br> ",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
            ]);
    }
}
