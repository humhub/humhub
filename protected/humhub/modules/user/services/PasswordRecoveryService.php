<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\libs\Helpers;
use humhub\libs\SafeBaseUrl;
use humhub\libs\UUID;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use Yii;

class PasswordRecoveryService
{
    public const SETTING_TOKEN = 'passwordRecoveryToken';
    public const TOKEN_MAX_LIFE_TIME = 24 * 60 * 60;
    public const LIMIT_EMAIL_SEND_TIME = 10 * 60;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Check if email sending is limited with 10 minute pause
     *
     * @return bool
     */
    public function isLimited(): bool
    {
        $savedToken = $this->getSavedToken();
        if ($savedToken === null) {
            return false;
        }

        return (int)$savedToken['time'] + self::LIMIT_EMAIL_SEND_TIME >= time();
    }

    /**
     * Sends a message with recovery info by e-mail
     *
     * @return bool
     */
    public function sendRecoveryInfo(): bool
    {
        // Switch to users language - if specified
        Yii::$app->setLanguage($this->user->language);

        $token = UUID::v4();

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/RecoverPassword',
            'text' => '@humhub/modules/user/views/mails/plaintext/RecoverPassword',
        ], [
            'user' => $this->user,
            'linkPasswordReset' => SafeBaseUrl::to(['/user/password-recovery/reset',
                'token' => $token,
                'guid' => $this->user->guid,
            ], true),
        ]);
        $mail->setTo($this->user->email);
        $mail->setSubject(Yii::t('UserModule.account', 'Password Recovery'));

        if ($mail->send()) {
            $this->user->getSettings()->set(self::SETTING_TOKEN, $token . '.' . time());
            return true;
        }

        return false;
    }

    public function getSavedToken(): ?array
    {
        // Saved token - Format: randomToken.generationTime
        $tokenData = $this->user->getSettings()->get(self::SETTING_TOKEN);

        if (!is_string($tokenData)) {
            return null;
        }

        $tokenData = explode('.', $tokenData);
        if (count($tokenData) !== 2) {
            return null;
        }

        return [
            'key' => $tokenData[0],
            'time' => $tokenData[1],
        ];
    }

    /**
     * Check the request token is valid for the User
     *
     * @param string|null $token
     * @return bool
     */
    public function checkToken(?string $token): bool
    {
        $savedToken = $this->getSavedToken();
        if ($savedToken === null) {
            return false;
        }

        if (!Helpers::same($savedToken['key'], $token)) {
            return false;
        }

        // Token must not be older than 24 hours
        return (int)$savedToken['time'] + self::TOKEN_MAX_LIFE_TIME >= time();
    }

    /**
     * Reset a password with checking a provided token
     *
     * @param Password $password
     * @return bool
     */
    public function reset(Password $password): bool
    {
        if (!$password->validate()) {
            return false;
        }

        $password->scenario = 'registration';
        $password->user_id = $this->user->id;
        $password->setPassword($password->newPassword);
        if ($password->save()) {
            $this->user->getSettings()->delete(self::SETTING_TOKEN);
            return true;
        }

        return false;
    }
}
