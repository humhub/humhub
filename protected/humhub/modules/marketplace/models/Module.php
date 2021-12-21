<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\models;

use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Class Module for not installed module
 * Used in order to initialise module date from array
 *
 * @property-read string $version
 * @property-read string $image
 * @property-read string $checkoutUrl
 *
 * @since 1.11
 */
class Module extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $latestVersion;

    /**
     * @var string
     */
    public $moduleImageUrl;

    /**
     * @var string
     */
    public $professional_only;

    /**
     * @var string
     */
    public $useCases;

    /**
     * @var string
     */
    public $featured;

    /**
     * @var string
     */
    public $showDisclaimer;

    /**
     * @var string
     */
    public $isThirdParty;

    /**
     * @var string
     */
    public $isCommunity;

    /**
     * @var string
     */
    public $isDeprecated;

    /**
     * @var string
     */
    public $marketplaceUrl;

    /**
     * @var string
     */
    public $latestCompatibleVersion;

    /**
     * @var string
     */
    public $price_eur;

    /**
     * @var array
     */
    public $categories;

    /**
     * @var string
     */
    public $purchased;

    /**
     * @var string
     */
    public $price_request_quote;

    /**
     * @var string
     */
    public $checkoutUrl;

    public function getVersion(): string
    {
        return $this->latestVersion;
    }

    public function getImage(): string
    {
        return empty($this->moduleImageUrl)
            ? Yii::getAlias('@web-static/img/default_module.jpg')
            : $this->moduleImageUrl;
    }

    public function isInstalled(): bool
    {
        return Yii::$app->moduleManager->hasModule($this->id);
    }

    public function isProFeature(): bool
    {
        return !empty($this->professional_only);
    }

    public function isProOnly(): bool
    {
        if (!$this->isProFeature()) {
            return false;
        }

        /* @var MarketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $licence = $marketplaceModule->getLicence();

        return $licence->type !== Licence::LICENCE_TYPE_PRO;
    }

    public function getCheckoutUrl(): string
    {
        return str_replace('-returnToUrl-', Url::to(['/marketplace/purchase/list'], true), $this->checkoutUrl);
    }
}