<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter;
use humhub\modules\user\Module;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\User;
use yii\web\ForbiddenHttpException;
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
    public $users;

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

    /**
     * Is a bulk action on multiple users
     * @var bool
     */
    protected $_isBulkAction = false;


    /**
     * @inerhitdoc
     * @param $usersId int|string|array
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct($usersId)
    {
        $this->admin = Yii::$app->user->getIdentity();

        if (is_array($usersId)) {
            $this->_isBulkAction = true;
        }

        if ($this->_isBulkAction) {
            $this->users = $this->getUsers($usersId);
        } else {
            $users = $this->getUsers([(int)$usersId]);
            $this->user = reset($users);
        }

        parent::__construct([]);
    }

    /**
     * @inerhitdoc
     * @throws \yii\base\InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function init()
    {
        parent::init();

        if (!$this->_isBulkAction) {
            if (!($this->user instanceof User)) {
                throw new NotFoundHttpException(Yii::t('AdminModule.base', 'User not found!'));
            }

            if ($this->user->status !== User::STATUS_NEED_APPROVAL) {
                throw new NotFoundHttpException(Yii::t('AdminModule.base', 'Invalid user state: {state}', ['state' => $this->user->status]));
            }
        }

        if (!($this->admin instanceof User)) {
            throw new ForbiddenHttpException();
        }

        if (!$this->admin->canApproveUsers()) {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * @param $ids array
     * @return array|User|User[]|null
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    private function getUsers($ids)
    {
        return User::find()
            ->andWhere(['user.id' => $ids, 'user.status' => User::STATUS_NEED_APPROVAL])
            ->administrableBy($this->admin)->all();
    }

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        if ($this->_isBulkAction) {
            return [];
        }

        return [
            [['subject', 'message'], 'required'],
        ];
    }

    /**
     * @inheritDoc
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
    public function approve(): bool
    {
        if (!$this->message) {
            $this->setApprovalDefaults();
        }

        if (!$this->validate()) {
            return false;
        }

        $this->user->status = User::STATUS_ENABLED;

        if ($this->validate() &&
            $this->user->save() &&
            $this->send()) {
            return true;
        }

        return false;
    }

    /**
     * Declines user by sending denial mail and deleting the user.
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function decline(): bool
    {
        if (!$this->message) {
            $this->setDeclineDefaults();
        }

        return $this->validate() &&
            $this->send() &&
            $this->user->delete();
    }

    /**
     * @return bool
     */
    public function bulkApprove()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->users as $user) {
            $this->user = $user;
            $this->setApprovalDefaults();
            $this->approve();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function bulkDecline()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->users as $user) {
            $this->user = $user;
            $this->setDeclineDefaults();
            $this->decline();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function send(): bool
    {
        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], [
            'message' => RichTextToEmailHtmlConverter::process($this->message)
        ]);
        $mail->setTo($this->user->email);
        $mail->setSubject($this->subject);
        return $mail->send();
    }

    /**
     * Sets the subject and message attribute texts for user approval
     * @return void
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

        $loginURL = Url::to(['/user/auth/login'], true);
        $loginLink = Html::a(urldecode($loginURL), $loginURL);
        $userName = Html::encode($this->user->displayName);
        $adminName = Html::encode($this->admin->displayName);

        if (!empty($module->settings->get('auth.registrationApprovalMailContent'))) {
            $this->message = Yii::t('AdminModule.user', $module->settings->get('auth.registrationApprovalMailContent'), [
                '{displayName}' => $userName,
                '{AdminName}' => $adminName,
                '{loginLink}' => $loginLink,
                '{loginURL}' => urldecode($loginURL),
                '{loginUrl}' => urldecode($loginURL),
            ]);
        } else {
            $this->message = static::getDefaultApprovalMessage($userName, $adminName, $loginURL);
        }

        Yii::$app->i18n->autosetLocale();
    }

    /**
     * Sets the subject and message attribute texts for user decline
     * @return void
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
    public static function getDefaultApprovalMessage($userDisplayName = '{displayName}', $adminDisplayName = '{AdminName}', $loginUrl = '{loginUrl}')
    {
        return Yii::t('AdminModule.user', "Hello {displayName},\n\n" .
            "Your account has been activated.\n\n" .
            "Click here to login:\n{loginUrl}\n\n" .
            "Kind Regards\n" .
            "{AdminName}\n\n",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
                '{loginUrl}' => $loginUrl,
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
        return Yii::t('AdminModule.user', "Hello {displayName},\n\n" .
            "Your account request has been declined.\n\n" .
            "Kind Regards\n" .
            "{AdminName}\n\n",
            [
                '{displayName}' => $userDisplayName,
                '{AdminName}' => $adminDisplayName,
            ]);
    }
}
