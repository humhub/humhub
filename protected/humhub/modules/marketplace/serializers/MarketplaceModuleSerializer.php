<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\serializers;

use humhub\modules\marketplace\models\Module;
use Yii;

/**
 * The wire representation of a marketplace module (see `docs/api/src/marketplace.yaml`).
 *
 * It describes the module as this installation sees it — the same for every administrator.
 * Field names avoid `status`, `url` and `response`: `humhub.client` merges a JSON answer into
 * its Response object, where those names are taken.
 *
 * @since 1.20
 */
class MarketplaceModuleSerializer
{
    public static function module(Module $module): array
    {
        $installedVersion = $module->findInstalledVersion();
        $coreModule = $installedVersion !== null ? Yii::$app->moduleManager->getModule($module->id, false) : null;
        $isEnabled = $coreModule !== null && $coreModule->getIsEnabled();
        $configUrl = $isEnabled ? (string)$coreModule->getConfigUrl() : '';

        return [
            'id' => (string)$module->id,
            'name' => (string)$module->name,
            'description' => (string)$module->description,
            'imageUrl' => $module->getImage(),
            'marketplaceUrl' => $module->marketplaceUrl ? (string)$module->marketplaceUrl : null,
            'categories' => is_array($module->categories) ? array_map('intval', $module->categories) : [],
            'useCases' => $module->getUseCaseList(),
            'latestVersion' => $module->latestVersion ? (string)$module->latestVersion : null,
            'latestCompatibleVersion' => $module->latestCompatibleVersion ? (string)$module->latestCompatibleVersion : null,
            'installedVersion' => $installedVersion,
            'isEnabled' => $isEnabled,
            'updateAvailable' => $module->isUpdateAvailable(),
            'configUrl' => $configUrl !== '' ? $configUrl : null,
            'checkoutUrl' => $module->checkoutUrl ? $module->getCheckoutUrl() : null,
            'price' => self::price($module),
            'purchased' => (bool)$module->purchased,
            'featured' => (bool)$module->featured,
            'licenceKey' => $module->licence_key ? (string)$module->licence_key : null,
            'badge' => $module->getBadge(),
            'isThirdParty' => (bool)$module->isThirdParty,
            'isCommunity' => (bool)$module->isCommunity,
            'availability' => $module->getAvailability(),
        ];
    }

    private static function price(Module $module): ?array
    {
        if (!empty($module->price_request_quote)) {
            return ['amount' => null, 'currency' => 'EUR', 'onRequest' => true];
        }

        if (!empty($module->price_eur)) {
            return ['amount' => (float)$module->price_eur, 'currency' => 'EUR', 'onRequest' => false];
        }

        return null;
    }
}
