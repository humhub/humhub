<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\UserSourceService;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Form model of the "General" tab of the account settings: username, e-mail address and introduction tour
 *
 * The fields start with the current values. A changed username is saved at once, a changed e-mail address once the
 * user confirmed it through the link sent to it. Changing either asks for the sudo mode (docs/develop/concept-sudo-mode.md).
 *
 * @since 0.9
 */
class AccountSettings extends Model
{
    /**
     * @var string|null
     * @since 1.20
     */
    public $username;

    /**
     * @var string|null
     * @since 1.20
     */
    public $email;

    public $show_introduction_tour;

    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->user !== null) {
            $this->username = $this->user->username;
            $this->email = $this->user->email;
            $this->show_introduction_tour = $this->getSettings()->get('hideTourPanel');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $usernameChanged = fn() => $this->isUsernameChanged();
        $emailChanged = fn() => $this->isEmailChanged();
        $others = ['!=', 'id', $this->user?->id];

        return [
            [['username', 'email'], 'trim'],
            ['username', 'required', 'when' => $usernameChanged],
            ['username', 'string', 'min' => $userModule->minimumUsernameLength, 'max' => $userModule->maximumUsernameLength, 'when' => $usernameChanged],
            ['username', 'match', 'pattern' => $userModule->validUsernameRegexp, 'message' => Yii::t('UserModule.base', 'Username contains invalid characters.'), 'enableClientValidation' => false, 'when' => $usernameChanged],
            ['username', 'unique', 'targetClass' => User::class, 'filter' => $others, 'message' => '{attribute} "{value}" is already in use!', 'when' => $usernameChanged],
            ['username', 'validateForbiddenUsername', 'when' => $usernameChanged],
            ['email', 'required', 'when' => $emailChanged],
            ['email', 'string', 'max' => 150, 'when' => $emailChanged],
            ['email', 'email', 'when' => $emailChanged],
            ['email', 'unique', 'targetClass' => User::class, 'filter' => $others, 'message' => '{attribute} "{value}" is already in use!', 'when' => $emailChanged],
            [['show_introduction_tour'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('UserModule.base', 'Username'),
            'email' => Yii::t('UserModule.account', 'E-Mail address'),
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
        ];
    }

    /**
     * @inheritdoc
     *
     * The username and the e-mail address only where the user's source allows changing them.
     */
    public function safeAttributes()
    {
        return array_values(array_filter(parent::safeAttributes(), fn($attribute) => match ($attribute) {
            'username' => $this->canChangeUsername(),
            'email' => $this->canChangeEmail(),
            default => true,
        }));
    }

    public function validateForbiddenUsername($attribute)
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        if (in_array(strtolower((string)$this->$attribute), $userModule->forbiddenUsernames)) {
            $this->addError($attribute, Yii::t('UserModule.account', 'You cannot use this username.'));
        }
    }

    /**
     * @since 1.20
     */
    public function canChangeUsername(): bool
    {
        return $this->user !== null && UserSourceService::getForUser($this->user)->canChangeUsername();
    }

    /**
     * @since 1.20
     */
    public function canChangeEmail(): bool
    {
        return $this->user !== null && UserSourceService::getForUser($this->user)->canChangeEmail();
    }

    /**
     * @return bool whether the introduction tour is enabled, so its setting is offered
     * @since 1.20
     */
    public function isTourAvailable(): bool
    {
        return Yii::$app->getModule('tour')->settings->get('enable') == 1;
    }

    /**
     * @since 1.20
     */
    public function isUsernameChanged(): bool
    {
        return $this->user !== null && trim((string)$this->username) !== $this->user->username;
    }

    /**
     * @since 1.20
     */
    public function isEmailChanged(): bool
    {
        return $this->user !== null && trim((string)$this->email) !== $this->user->email;
    }

    /**
     * @return bool whether the username or the e-mail address is changed, which asks for the sudo mode
     * @since 1.20
     */
    public function changesCredentials(): bool
    {
        return $this->isUsernameChanged() || $this->isEmailChanged();
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->isEmailChanged()) {
            $this->sendEmailConfirmation();
        }

        if ($this->isUsernameChanged()) {
            $this->changeUsername();
        }

        $this->getSettings()->set('hideTourPanel', $this->show_introduction_tour);

        return true;
    }

    /**
     * Sends the link that applies the new e-mail address to it (`user/account/change-email-validate`)
     */
    private function sendEmailConfirmation(): void
    {
        $token = md5(Yii::$app->settings->get('secret') . $this->user->guid . $this->email);

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/ChangeEmail',
            'text' => '@humhub/modules/user/views/mails/plaintext/ChangeEmail',
        ], [
            'user' => $this->user,
            'newEmail' => $this->email,
            'approveUrl' => Url::to(['/user/account/change-email-validate', 'email' => $this->email, 'token' => $token], true),
        ]);
        $mail->setTo($this->email);
        $mail->setSubject(Yii::t('UserModule.account', 'E-Mail change'));
        $mail->send();
    }

    private function changeUsername(): void
    {
        $this->user->username = $this->username;
        $this->user->save();

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/ChangeUsername',
            'text' => '@humhub/modules/user/views/mails/plaintext/ChangeUsername',
        ], [
            'user' => $this->user,
            'newUsername' => $this->username,
        ]);
        $mail->setTo($this->user->email);
        $mail->setSubject(Yii::t('UserModule.account', 'Username has been changed'));
        $mail->send();
    }

    private function getSettings(): ContentContainerSettingsManager
    {
        return Yii::$app->getModule('tour')->settings->contentContainer($this->user);
    }
}
