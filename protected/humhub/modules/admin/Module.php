<?php

namespace humhub\modules\admin;

class Module extends \humhub\components\Module
{

    public $controllerNamespace = 'humhub\modules\admin\controllers';
    public $defaultRoute = 'index';
    public $isCoreModule = true;

    /**
     * Should the marketplace be enabled
     *
     * @var boolean
     */
    public $marketplaceEnabled = true;

    /**
     * URL to HumHub Marketplace API
     *
     * @var string
     */
    public $marketplaceApiUrl = "https://www.humhub.com/api/v1/modules/";

    /**
     * Enforce valid marketplace ssl certificate
     *
     * @var boolean
     */
    public $marketplaceApiValidateSsl = true;

    public function init()
    {
        parent::init();
    }

}
