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

        if (Setting::Get('enabled', 'proxy')) {
            $options[CURLOPT_PROXY] = Setting::Get('server', 'proxy');
            $options[CURLOPT_PROXYPORT] = Setting::Get('port', 'proxy');
            if (defined('CURLOPT_PROXYUSERNAME')) {
                $options[CURLOPT_PROXYUSERNAME] = Setting::Get('user', 'proxy');
            }
            if (defined('CURLOPT_PROXYPASSWORD')) {
                $options[CURLOPT_PROXYPASSWORD] = Setting::Get('password', 'proxy');
            }
            if (defined('CURLOPT_NOPROXY')) {
                $options[CURLOPT_NOPROXY] = Setting::Get('noproxy', 'proxy');
            }
        }

        return $options;
    }

}
