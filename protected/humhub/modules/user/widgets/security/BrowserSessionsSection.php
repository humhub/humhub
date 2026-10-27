<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\services\UserSessionService;
use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * The browser sessions of the current user
 *
 * @since 1.20
 */
class BrowserSessionsSection extends SecuritySection
{
    private ?array $sessions = null;

    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Browser sessions');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'These devices are currently logged in to your account. Log out any session you do not recognize.');
    }

    /**
     * "Log out all other sessions", while there are others.
     */
    public function getActions(): string
    {
        if (count($this->getSessions()) < 2) {
            return '';
        }

        return (string)Button::light(Yii::t('UserModule.account', 'Log out all other sessions'))
            ->icon('logout')
            ->link(['/user/security/terminate-other-sessions'])
            ->options(['data-method' => 'POST'])
            ->confirm(
                Yii::t('UserModule.account', 'Log out all other sessions?'),
                Yii::t('UserModule.account', 'You will stay logged in on this device only.'),
            )
            ->sm();
    }

    public function isVisible(): bool
    {
        return $this->getService()->isAvailable();
    }

    protected function renderContent(): string
    {
        return $this->render('browser-sessions', ['sessions' => $this->getSessions()]);
    }

    private function getSessions(): array
    {
        return $this->sessions ??= $this->getService()->getActiveSessions();
    }

    private function getService(): UserSessionService
    {
        return new UserSessionService(Yii::$app->user->getIdentity());
    }
}
