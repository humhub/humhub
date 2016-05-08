<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use humhub\models\Setting;

/**
 * CURLHelper
 *
 * @author luke
 */
class CURLHelper
{

    /**
     * Returns CURL Default Options
     * 
     * @return array
     */
    public static function getOptions()
    {
        $options = array(
            CURLOPT_SSL_VERIFYPEER => (Yii::$app->params['curl']['validateSsl']) ? true : false,
            CURLOPT_SSL_VERIFYHOST => (Yii::$app->params['curl']['validateSsl']) ? 2 : 0,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CAINFO => Yii::getAlias('@humhub/config/cacert.pem')
        );

        if (Setting::Get('proxy.enabled')) {
            $options[CURLOPT_PROXY] = Setting::Get('proxy.server');
            $options[CURLOPT_PROXYPORT] = Setting::Get('proxy.port');
            if (defined('CURLOPT_PROXYUSERNAME')) {
                $options[CURLOPT_PROXYUSERNAME] = Setting::Get('proxy.user');
            }
            if (defined('CURLOPT_PROXYPASSWORD')) {
                $options[CURLOPT_PROXYPASSWORD] = Setting::Get('proxy.password');
            }
            if (defined('CURLOPT_NOPROXY')) {
                $options[CURLOPT_NOPROXY] = Setting::Get('proxy.noproxy');
            }
        }

        return $options;
    }

}
