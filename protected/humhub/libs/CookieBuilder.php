<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\di\Container;
use yii\web\Cookie;

/**
 * DI Helper to bake secure cookies.
 *
 * @since 1.13
 */
class CookieBuilder
{
    /**
     * @param $container Container
     * @param $params array
     * @param $config
     * @return Cookie
     */
    public static function build($container, $params, $config)
    {
        $cookie = new Cookie($config);

        if (Yii::$app->request->autoEnsureSecureConnection
            && Yii::$app->request->isSecureConnection) {
            $cookie->secure = true;
        }

        return $cookie;
    }
}
