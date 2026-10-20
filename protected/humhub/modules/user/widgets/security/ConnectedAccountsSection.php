<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\authclient\BaseFormClient;
use Yii;
use yii\authclient\ClientInterface;

/**
 * @since 1.20
 */
class ConnectedAccountsSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('UserModule.base', 'Connected accounts');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.base', 'Here you can connect to external service provider for using external services like a single sign on authentication.');
    }

    public function isVisible(): bool
    {
        return $this->getAuthClients() !== [];
    }

    protected function renderContent(): string
    {
        $activeAuthClientIds = [];
        foreach (Yii::$app->user->getAuthClientUserService()->getClients() as $authClient) {
            $activeAuthClientIds[] = $authClient->getId();
        }

        return $this->render('connected-accounts', [
            'authClients' => $this->getAuthClients(),
            'currentAuthProviderId' => Yii::$app->user->getCurrentAuthClient()?->getId() ?? '',
            'activeAuthClientIds' => $activeAuthClientIds,
        ]);
    }

    /**
     * @return ClientInterface[] auth clients other than the login form
     */
    private function getAuthClients(): array
    {
        return array_values(array_filter(
            Yii::$app->get('authClientCollection')->getClients(),
            fn($client) => !$client instanceof BaseFormClient,
        ));
    }
}
