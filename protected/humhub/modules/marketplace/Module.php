<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\Module as BaseModule;
use humhub\modules\marketplace\components\HumHubApiClient;
use humhub\modules\marketplace\components\LicenceManager;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\models\Licence;
use Yii;

/**
 * The Marketplace modules contains all the capabilities to interact with the offical HumHub marketplace.
 * The core functions are the ability to easily install or update modules from the remote module directory.
 *
 * @property-read Licence $licence
 * @property OnlineModuleManager $onlineModuleManager
 * @since 1.4
 */
class Module extends BaseModule
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\marketplace\controllers';

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var string path to store marketplace modules
     * If the param 'moduleMarketplacePath' is set this value will be used.
     * Note: The path must also be added to the module autoloader `Yii::$app->params['moduleAutoloadPaths']`.
     */
    public $modulesPath = '@app/modules';

    /**
     * @var bool
     */
    public $enabled = true;

    /**
     * @var string download path for marketplace modules
     */
    public $modulesDownloadPath = '@runtime/module_downloads';

    /**
     * @var bool
     */
    public $hideLegacyModules = true;

    /**
     * @since 1.8
     * @var array A list of module ids that cannot be installed.
     */
    public $moduleBlacklist = [];
    private $_onlineModuleManager = null;
    private $_humhubApi = null;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('MarketplaceModule.base', 'Marketplace');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->params['humhub']['apiEnabled'] !== true) {
            $this->enabled = false;
        }

        if (!empty(Yii::$app->params['moduleMarketplacePath'])) {
            $this->modulesPath = Yii::$app->params['moduleMarketplacePath'];
        }
    }

    /**
     * @return bool
     * @deprecated since v1.16; use `static::isMarketplaceEnabled()` instead
     * @see static::isMarketplaceEnabled()
     */
    public static function isEnabled(): bool
    {
        return static::isMarketplaceEnabled();
    }

    /**
     * @return bool
     */
    public static function isMarketplaceEnabled(): bool
    {
        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        return $marketplaceModule && $marketplaceModule->enabled;
    }

    /**
     * @return OnlineModuleManager
     */
    public function getOnlineModuleManager()
    {
        if ($this->_onlineModuleManager === null) {
            $this->_onlineModuleManager = new OnlineModuleManager();
        }

        return $this->_onlineModuleManager;
    }


    /**
     * Returns the currently active Licence object
     *
     * @return Licence
     */
    public function getLicence()
    {
        return LicenceManager::get();
    }

    /**
     * Returns the public HUmhub API (Marketplace, Updater & Co)
     *
     * @return HumHubApiClient
     */
    public function getHumHubApi()
    {
        if ($this->_humhubApi === null) {
            $this->_humhubApi = new HumHubApiClient();
        }

        return $this->_humhubApi;
    }
}
