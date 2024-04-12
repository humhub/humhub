<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

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
     * Http header name for view context information
     *
     * @see \humhub\modules\ui\view\components\View::$viewContext
     */
    public const HEADER_VIEW_CONTEXT = 'HUMHUB-VIEW-CONTEXT';
    /**
     * Whenever a secure connection is detected, force it.
     *
     * @var bool
     * @since 1.13
     */
    public $autoEnsureSecureConnection = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->isInstalled()) {
            $secret = Yii::$app->settings->get('secret');
            if ($secret != "") {
                $this->cookieValidationKey = $secret;
            }
        }

        if ($this->cookieValidationKey == '') {
            $this->cookieValidationKey = 'installer';
        }

        if (
            defined('YII_ENV_TEST') && YII_ENV_TEST && $_SERVER['SCRIPT_FILENAME'] === 'index-test.php' && in_array(
                $_SERVER['SCRIPT_NAME'],
                ['/sw.js', '/offline.pwa.html', '/manifest.json'],
                true,
            )
        ) {
            $this->setScriptUrl('/index.php');
        }
    }

    /**
     * @return string|null the value of http header `HUMHUB-VIEW-CONTEXT`
     */
    public function getViewContext()
    {
        return $this->getHeaders()->get(static::HEADER_VIEW_CONTEXT);
    }
}
