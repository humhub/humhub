<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\services\UserSessionService;
use Yii;

/**
 * The browser sessions of the current user
 *
 * @since 1.20
 */
class BrowserSessionsSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Browser sessions');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'These devices are currently logged in to your account. Log out any session you do not recognize.');
    }

    public function isVisible(): bool
    {
        return $this->getService()->isAvailable();
    }

    protected function renderContent(): string
    {
        return $this->render('browser-sessions', ['sessions' => $this->getService()->getActiveSessions()]);
    }

    private function getService(): UserSessionService
    {
        return new UserSessionService(Yii::$app->user->getIdentity());
    }
}
