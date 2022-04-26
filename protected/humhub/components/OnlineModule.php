<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\marketplace\models\Licence;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;
use yii\base\Component;

/**
 * Online Module class for load module date from marketplace
 *
 * @property-read bool $isInstalled
 * @property-read bool $isProOnly
 * @property-read bool $isFeatured
 * @property-read bool $isThirdParty
 * @property-read bool $isPartner
 * @property-read bool $isDeprecated
 * @property-read array $categories
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 1.11
 */
class OnlineModule extends Component
{
    /**
     * @var Module
     */
    public $module;

    /**
     * @var array the cached info loaded from online
     */
    private $_onlineInfo = null;

    /**
     * Get online info of the Module
     *
     * @param string|null $field Null - to return all fields, String - to return a value of the requested field:
     *        - id
     *        - name
     *        - description
     *        - useCases
     *        - featured
     *        - showDisclaimer
     *        - isThirdParty
     *        - isCommunity
     *        - isPartner
     *        - isDeprecated
     *        - latestVersion
     *        - moduleImageUrl
     *        - marketplaceUrl
     *        - latestCompatibleVersion
     *        - purchased
     *        - price_eur
     *        - price_request_quote
     *        - checkoutUrl
     *        - professional_only
     *        - categories
     * @return array|null|string
     */
    public function info(?string $field = null)
    {
        if ($this->_onlineInfo === null) {
            /* @var MarketplaceModule $marketplaceModule */
            $marketplaceModule = Yii::$app->getModule('marketplace');
            if (!($marketplaceModule instanceof MarketplaceModule && $marketplaceModule->enabled)) {
                return null;
            }

            if ($this->module instanceof ModelModule) {
                $this->_onlineInfo = (array)$this->module;
            } else {
                $onlineModules = $marketplaceModule->onlineModuleManager->getModules();
                $this->_onlineInfo = isset($onlineModules[$this->module->id]) ? $onlineModules[$this->module->id] : [];
            }
        }

        if ($field === null) {
            return $this->_onlineInfo;
        }

        return $this->_onlineInfo[$field] ?? null;
    }

    public function getIsInstalled(): bool
    {
        return Yii::$app->moduleManager->hasModule($this->module->id);
    }

    public function isProOnly(): bool
    {
        if (empty($this->info('professional_only'))) {
            return false;
        }

        return true;
    }

    public function getIsProOnly(): bool
    {
        return $this->isProOnly();
    }

    public function getCategories(): array
    {
        $onlineInfo = $this->info();
        return $onlineInfo['categories'] ?? [];
    }

    public function getIsFeatured(): bool
    {
        return (bool) $this->info('featured');
    }

    public function getIsThirdParty(): bool
    {
        $isThirdParty = $this->info('isThirdParty');
        return $isThirdParty || $isThirdParty === null;
    }

    public function getIsPartner(): bool
    {
        return (bool) $this->info('isPartner');
    }

    public function getIsDeprecated(): bool
    {
        return (bool) $this->info('isDeprecated');
    }
}
