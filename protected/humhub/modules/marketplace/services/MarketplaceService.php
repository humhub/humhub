<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\components\SettingsManager;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;

/**
 * @since 1.15
 */
class MarketplaceService
{
    public const API_URL_ADD_LICENCE_KEY = 'v1/modules/registerPaid';

    /**
     * @since 1.20
     */
    public const SETTING_KEYS = ['includeBetaUpdates', 'includeCommunityModules'];

    public function getMarketplaceModule(): MarketplaceModule
    {
        return Yii::$app->getModule('marketplace');
    }

    public function getSettings(): SettingsManager
    {
        return $this->getMarketplaceModule()->settings;
    }

    /**
     * The marketplace settings as the API and the island see them.
     *
     * @since 1.20
     */
    public function getPublicSettings(): array
    {
        $settings = $this->getSettings();

        return [
            'includeBetaUpdates' => (bool)$settings->get('includeBetaUpdates', false),
            'includeCommunityModules' => (bool)$settings->get('includeCommunityModules', false),
        ];
    }

    /**
     * Stores the given settings (keys of {@see self::SETTING_KEYS}, others are ignored) and drops
     * what they invalidate: the category counts always, the module list when beta versions are
     * switched (humhub.com is asked for a different list then).
     *
     * @param array<string, bool> $values
     * @since 1.20
     */
    public function updateSettings(array $values): array
    {
        $current = $this->getPublicSettings();

        foreach ($values as $key => $value) {
            if (in_array($key, self::SETTING_KEYS, true)) {
                $this->getSettings()->set($key, (bool)$value);
            }
        }

        if (array_key_exists('includeBetaUpdates', $values) && (bool)$values['includeBetaUpdates'] !== $current['includeBetaUpdates']) {
            Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_MODULES);
        }
        Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_CATEGORIES);

        return $this->getPublicSettings();
    }

    public static function addLicenceKey(?string $licenceKey): array
    {
        $result = [
            'licenceKey' => $licenceKey,
            'hasError' => false,
            'unreachable' => false,
            'message' => '',
        ];

        if (empty($licenceKey)) {
            return $result;
        }

        $response = HumHubAPI::request(self::API_URL_ADD_LICENCE_KEY, ['licenceKey' => $licenceKey]);

        if (!isset($response['status'])) {
            $result['hasError'] = true;
            $result['unreachable'] = true;
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

    public function refreshPendingModuleUpdateCount(?int $count = null)
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
