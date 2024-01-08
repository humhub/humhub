<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\models;

use humhub\interfaces\ModuleInfoInterface;
use humhub\modules\marketplace\Module as MarketplaceModule;
use humhub\modules\marketplace\services\FilterService;
use humhub\widgets\Link;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Class Module for not installed module
 * Used in order to initialise module data from array
 *
 * @property-read string $version
 * @property-read string $image
 * @property-read string $checkoutUrl
 * @property-read bool $isNonFree
 * @property-read bool $isActivated
 *
 * @since 1.11
 */
class Module extends Model implements ModuleInfoInterface
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
     * @var bool
     */
    public $isThirdParty;

    /**
     * @var string
     */
    public $isCommunity;

    /**
     * @var bool
     */
    public $isPartner;

    /**
     * @var bool
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

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (!property_exists($this, $name)) {
                // Exclude new unknown property from marketplace API to avoid error
                unset($config[$name]);
            }
        }

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     * @since 1.16
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     * @since 1.16
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     * @since 1.16
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     * @since 1.16
     */
    public function getKeywords(): array
    {
        return [];
    }

    public function getIsNonFree(): bool
    {
        return (!empty($this->price_eur) || !empty($this->price_request_quote));
    }

    public function getVersion(): string
    {
        return $this->latestVersion;
    }

    public function getInstalledVersion(): string
    {
        return Yii::$app->moduleManager->getModule($this->id)->getVersion();
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

    public function getIsActivated(): bool
    {
        return Yii::$app->moduleManager->getModule($this->id)->isActivated;
    }

    public function getConfigUrl(): string
    {
        return Yii::$app->moduleManager->getModule($this->id)->getConfigUrl();
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

    public function getFilterService(): FilterService
    {
        return new FilterService($this);
    }

    public function marketplaceLink(string $text): Link
    {
        return Link::asLink($text, $this->marketplaceUrl)->blank();
    }
}
