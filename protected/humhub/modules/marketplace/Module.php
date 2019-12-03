<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\Module as BaseModule;
use humhub\models\Setting;
use humhub\modules\marketplace\models\Licence;
use humhub\modules\marketplace\components\OnlineModuleManager;
use Yii;

/**
 * The Marketplace modules contains all the capabilities to interact with the offical HumHub marketplace.
 * The core functions are the ability to easily install or update modules from the remote module directory.
 *
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
     */
    public $modulesPath = '@app/modules';

    /**
     * @var bool
     */
    public $enabled = true;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('MarketplaceModule.base', 'Marketplace');
    }

    private $_onlineModuleManager = null;

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
     * @return Licence
     */
    public function getLicence()
    {
        Licence::fetch();

        $l = new Licence();

        $l->licenceKey = $this->settings->get('licenceKey');
        $l->licencedTo = $this->settings->get('licencedTo');

        if (!empty($l->licencedTo)) {
            $l->maxUsers = (int)$this->settings->get('maxUsers');
            $l->type = Licence::LICENCE_TYPE_PRO;
        } else {
            $l->type = Licence::LICENCE_TYPE_CE;

            if (Yii::$app->hasModule('enterprise')) {
                /** @var \humhub\modules\enterprise\Module $enterprise */
                $enterprise = Yii::$app->getModule('enterprise');
                if ($enterprise->settings->get('licence') !== null && $enterprise->settings->get('licence_valid') == 1) {
                    $l->type = Licence::LICENCE_TYPE_EE;
                }
            }
        }

        return $l;
    }
}
