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
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class PasswordRecoveryService
{
    public ?User $user;

    public function __construct(?User $user)
    {
        $this->user = $user;
    }

    private function getSettingsManager(): ContentContainerSettingsManager
    {
        /* @var Module $userModule */
        $userModule = Yii::$app->getModule('user');

        return $userModule->settings->contentContainer($this->user);
    }

    /**
     * Sends a message with recovery info by e-mail
     *
     * @return bool
     */
    public function sendRecoveryInfo(): bool
    {
        if ($this->user === null) {
            return false;
        }

        // Switch to users language - if specified
        Yii::$app->setLanguage($this->user->language);

        $token = UUID::v4();

        $this->getSettingsManager()->set('passwordRecoveryToken', $token . '.' . time());

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/RecoverPassword',
            'text' => '@humhub/modules/user/views/mails/plaintext/RecoverPassword'
        ], [
            'user' => $this->user,
            'linkPasswordReset' => SafeBaseUrl::to(['/user/password-recovery/reset',
                'token' => $token,
                'guid' => $this->user->guid
            ], true)
        ]);
        $mail->setTo($this->user->email);
        $mail->setSubject(Yii::t('UserModule.account', 'Password Recovery'));

        return $mail->send();
    }

    /**
     * Check the request token is valid for the User
     *
     * @param string|null $token
     * @return bool
     */
    private function checkToken(?string $token): bool
    {
        if ($this->user === null) {
            return false;
        }

        // Saved token - Format: randomToken.generationTime
        $savedTokenInfo = $this->getSettingsManager()->get('passwordRecoveryToken');
        if (!$savedTokenInfo) {
            return false;
        }

        list($generatedToken, $generationTime) = explode('.', $savedTokenInfo);
        if (!Helpers::same($generatedToken, $token)) {
            return false;
        }

        // Token must not be older than 24 hours
        return (int) $generationTime + (24 * 60 * 60) >= time();
    }


    /**
     * Reset a password with checking a provided token
     *
     * @param string|null $token
     * @return bool
     * @throws HttpException
     */
    public function reset(Password $password, ?string $token): bool
    {
        if (!$this->checkToken($token)) {
            throw new NotFoundHttpException(Yii::t('UserModule.base', 'It looks like you clicked on an invalid password reset link. Please try again.'));
        }

        $password->scenario = 'registration';

        if ($password->load(Yii::$app->request->post()) && $password->validate()) {
            $password->user_id = $this->user->id;
            $password->setPassword($password->newPassword);
            if ($password->save()) {
                $this->getSettingsManager()->delete('passwordRecoveryToken');
                return true;
            }
        }

        return false;
    }
}
