<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\components\SettingsManager;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;

/**
 * @since 1.15
 */
class MarketplaceService
{
    public const API_URL_ADD_LICENCE_KEY = 'v1/modules/registerPaid';

    public function getMarketplaceModule(): MarketplaceModule
    {
        return Yii::$app->getModule('marketplace');
    }

    public function getSettings(): SettingsManager
    {
        return $this->getMarketplaceModule()->settings;
    }

    public static function addLicenceKey(?string $licenceKey): array
    {
        $result = [
            'licenceKey' => $licenceKey,
            'hasError' => false,
            'message' => '',
        ];

        if (empty($licenceKey)) {
            return $result;
        }

        $response = HumHubAPI::request(self::API_URL_ADD_LICENCE_KEY, ['licenceKey' => $licenceKey]);

        if (!isset($response['status'])) {
            $result['hasError'] = true;
            $result['message'] = Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!');
            return $result;
        }

        if ($response['status'] !== 'ok' && $response['status'] !== 'created') {
            $result['hasError'] = true;
            $result['message'] = Yii::t('MarketplaceModule.base', 'Invalid module license key!');
            return $result;
        }

        $result['licenceKey'] = '';
        $result['message'] = Yii::t('MarketplaceModule.base', 'Module license added!');
        return $result;
    }

    public function refreshPendingModuleUpdateCount(int $count = null)
    {
        if (MarketplaceModule::isMarketplaceEnabled()) {
            if ($count === null) {
                $count = count($this->getMarketplaceModule()->onlineModuleManager->getModuleUpdates());
            }

            $this->getSettings()->set('pendingModuleUpdateCount', $count);
        }
    }

    public function getPendingModuleUpdateCount(): int
    {
        return MarketplaceModule::isMarketplaceEnabled()
            ? (int)$this->getSettings()->get('pendingModuleUpdateCount')
            : 0;
    }
}
