<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\models\Setting;
use Yii;

/**
 * @inheritdoc
 *
 *
 * @author luke
 */
class Request extends \yii\web\Request
{
    /**
     * @var bool automatically sets cookie secure flag when on secure connection
     */
    public $autoSetCookieSecureFlag = true;

    /**
     * Http header name for view context information
     * @see \humhub\modules\ui\view\components\View::$viewContext
     */
    const HEADER_VIEW_CONTEXT = 'HUMHUB-VIEW-CONTEXT';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Setting::isInstalled()) {
            $secret = Yii::$app->settings->get('secret');
            if ($secret != "") {
                $this->cookieValidationKey = $secret;
            }
        }

        if ($this->cookieValidationKey == '') {
            $this->cookieValidationKey = 'installer';
        }

    }

    /**
     * @inheritDoc
     */
    protected function createCsrfCookie($token)
    {
        if ($this->autoSetCookieSecureFlag && $this->isSecureConnection) {
            $this->csrfCookie['secure'] = true;
        }
        return parent::createCsrfCookie($token);
    }

    /**
     * @return string|null the value of http header `HUMHUB-VIEW-CONTEXT`
     */
    public function getViewContext()
    {
        return $this->getHeaders()->get(static::HEADER_VIEW_CONTEXT);
    }
}
