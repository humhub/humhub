<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\models;

use humhub\helpers\Html;
use humhub\modules\marketplace\Module as MarketplaceModule;
use humhub\services\ModuleDiscoveryService;
use humhub\widgets\Icon;
use humhub\widgets\bootstrap\Link;
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
 * @property-read bool $isEnabled
 * @property-read string[] $useCaseList
 *
 * @since 1.11
 */
class Module extends Model
{
    /**
     * What a not yet installed module offers, see {@see self::getAvailability()}.
     * @since 1.20
     */
    public const AVAILABILITY_INSTALL = 'install';
    public const AVAILABILITY_BUY = 'buy';
    public const AVAILABILITY_PROFESSIONAL_EDITION = 'professionalEdition';
    public const AVAILABILITY_INCOMPATIBLE = 'incompatible';

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

    /**
     * @var string|null the licence key of a purchased module
     * @since 1.20
     */
    public $licence_key;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getKeywords(): array
    {
        return $this->isInstalled()
            ? Yii::$app->moduleManager->getModule($this->id)->getKeywords()
            : [];
    }

    public function getIsNonFree(): bool
    {
        return (!empty($this->price_eur) || !empty($this->price_request_quote));
    }

    /**
     * The use cases humhub.com lists for this module (wire format: a comma-separated string,
     * e.g. `"intranet,education"`), trimmed, non-empty and lowercased.
     *
     * @return string[]
     * @since 1.20
     */
    public function getUseCaseList(): array
    {
        $useCases = array_map(
            static fn(string $useCase) => strtolower(trim($useCase)),
            explode(',', (string)$this->useCases),
        );

        return array_values(array_filter($useCases, static fn(string $useCase) => $useCase !== ''));
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
            ? Yii::$app->assetManager->getPublishedUrl('@humhub/resources') . '/img/default_module.jpg'
            : $this->moduleImageUrl;
    }

    public function isInstalled(): bool
    {
        return Yii::$app->moduleManager->hasModule($this->id);
    }

    public function isMarketplaced(): bool
    {
        /* @var MarketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        return $this->latestCompatibleVersion
            && !($this->isDeprecated && $marketplaceModule->hideLegacyModules);
    }

    /**
     * @since v1.16
     */
    public function getIsEnabled(): bool
    {
        return Yii::$app->moduleManager->getModule($this->id)->getIsEnabled();
    }

    public function getConfigUrl(): string
    {
        return Yii::$app->moduleManager->getModule($this->id)->getConfigUrl();
    }

    public function isProFeature(): bool
    {
        return !empty($this->professional_only);
    }

    public function getCheckoutUrl(): string
    {
        return str_replace('-returnToUrl-', Url::to(['/marketplace/browse', 'tag' => 'purchased'], true), $this->checkoutUrl);
    }

    /**
     * @return string|null the version installed in this instance, `null` if it is not installed
     * @since 1.20
     */
    public function findInstalledVersion(): ?string
    {
        if (!$this->isInstalled()) {
            return null;
        }

        $installedModule = Yii::$app->moduleManager->getModule($this->id, false);

        return $installedModule !== null
            ? (string)$installedModule->getVersion()
            : ModuleDiscoveryService::findInstalledVersion($this->id);
    }

    /**
     * @since 1.20
     */
    public function isUpdateAvailable(): bool
    {
        $installedVersion = $this->findInstalledVersion();

        return $installedVersion !== null
            && !empty($this->latestCompatibleVersion)
            && version_compare((string)$this->latestCompatibleVersion, $installedVersion, '>');
    }

    /**
     * The one label a card shows, in the precedence the marketplace always used. Featured is
     * not part of this precedence — it is shown as a star alongside the badge (see
     * {@see self::$featured}).
     *
     * @return string `professional`, `official`, `partner`, `deprecated`, `community` or `none`
     * @since 1.20
     */
    public function getBadge(): string
    {
        return match (true) {
            $this->isProFeature() => 'professional',
            !$this->isThirdParty => 'official',
            (bool)$this->isPartner => 'partner',
            (bool)$this->isDeprecated => 'deprecated',
            (bool)$this->isCommunity => 'community',
            default => 'none',
        };
    }

    /**
     * What installing this module takes — evaluated here so no client re-implements the rule.
     *
     * @return string one of the `AVAILABILITY_*` constants
     * @since 1.20
     */
    public function getAvailability(): string
    {
        if (empty($this->latestCompatibleVersion)) {
            return self::AVAILABILITY_INCOMPATIBLE;
        }

        /** @var MarketplaceModule $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        if ($this->isProFeature() && $marketplaceModule->getLicence()->type === Licence::LICENCE_TYPE_CE) {
            return self::AVAILABILITY_PROFESSIONAL_EDITION;
        }

        if ($this->getIsNonFree() && !$this->purchased) {
            return self::AVAILABILITY_BUY;
        }

        return self::AVAILABILITY_INSTALL;
    }

    /**
     * Builds a link to the module's marketplace page.
     *
     * Module metadata originates from the remote marketplace API and is therefore
     * untrusted. The label is encoded by default; pass $encode = false only when
     * $text is already safe markup generated locally (e.g. Html::img()).
     *
     * @deprecated since 1.20, the marketplace cards are rendered by the Vue island
     */
    public function marketplaceLink(string $text, bool $encode = true): Link
    {
        return Link::to($text, $this->marketplaceUrl)
            ->encodeLabel($encode)
            ->blank();
    }

    /**
     * @deprecated since 1.20, the marketplace cards are rendered by the Vue island
     */
    public function marketplaceImage(): Link
    {
        return $this->marketplaceLink(Html::img($this->image, [
            'class' => 'rounded',
            'data-src' => 'holder.js/94x94',
            'style' => 'width:94px;height:94px',
            'role' => 'presentation',
        ]), false)->options([
            'aria-hidden' => true,
            'tabindex' => -1,
        ]);
    }

    /**
     * @deprecated since 1.20, the marketplace cards are rendered by the Vue island
     */
    public function marketplaceName(): Link
    {
        $name = Html::encode($this->name);
        $label = $this->name;

        if ($this->featured) {
            $name .= ' ' . Html::tag('span', Icon::get('star')->color('info'), ['aria-hidden' => true]);
            $label .= ' — ' . Yii::t('MarketplaceModule.base', 'Featured');
        }

        return $this->marketplaceLink($name, false)
            ->options(['aria-label' => $label]);
    }
}
