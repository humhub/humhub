<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

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
        $options = [
            CURLOPT_SSL_VERIFYPEER => (Yii::$app->params['curl']['validateSsl']) ? true : false,
            CURLOPT_SSL_VERIFYHOST => (Yii::$app->params['curl']['validateSsl']) ? 2 : 0,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ];

        if (Yii::$app->settings->get('proxyEnabled')) {
            $options[CURLOPT_PROXY] = Yii::$app->settings->get('proxyServer');
            $options[CURLOPT_PROXYPORT] = Yii::$app->settings->get('proxyPort');
            if (defined('CURLOPT_PROXYUSERNAME')) {
                $options[CURLOPT_PROXYUSERNAME] = Yii::$app->settings->get('proxyUser');
            }
            if (defined('CURLOPT_PROXYPASSWORD')) {
                $options[CURLOPT_PROXYPASSWORD] = Yii::$app->settings->get('proxyPassword');
            }
            if (defined('CURLOPT_NOPROXY')) {
                $options[CURLOPT_NOPROXY] = Yii::$app->settings->get('proxyNoproxy');
            }
        }

        return $options;
    }
}
