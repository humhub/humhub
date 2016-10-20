<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\libs;

use Yii;
use yii\helpers\Json;
use humhub\libs\CURLHelper;


/**
 * HumHubAPI provides access to humhub.com for fetching available modules or latest version.
 *
 * @author luke
 */
class HumHubAPI
{

    /**
     * HumHub API
     *
     * @param string $action
     * @param array $params
     * @return array
     */
    public static function request($action, $params = [])
    {
        if (!Yii::$app->params['humhub']['apiEnabled']) {
            return [];
        }

        $url = Yii::$app->params['humhub']['apiUrl'] . '/' . $action;
        $params['version'] = urlencode(Yii::$app->version);
        $params['installId'] = Yii::$app->getModule('admin')->settings->get('installationId');

        $url .= '?';
        foreach ($params as $name => $value) {
            $url .= urlencode($name) . '=' . urlencode($value)."&";
        }
        try {
            $http = new \Zend\Http\Client($url, array(
                'adapter' => '\Zend\Http\Client\Adapter\Curl',
                'curloptions' => CURLHelper::getOptions(),
                'timeout' => 30
            ));

            $response = $http->send();
            $json = $response->getBody();
        } catch (\Zend\Http\Client\Adapter\Exception\RuntimeException $ex) {
            Yii::error('Could not connect to HumHub API! ' . $ex->getMessage());
            return [];
        } catch (Exception $ex) {
            Yii::error('Could not get HumHub API response! ' . $ex->getMessage());
            return [];
        }

        try {
            return Json::decode($json);
        } catch (\yii\base\InvalidParamException $ex) {
            Yii::error('Could not parse HumHub API response! ' . $ex->getMessage());
            return [];
        }
    }

    /**
     * Fetch latest HumHub version online
     *
     * @return string latest HumHub Version
     */
    public static function getLatestHumHubVersion()
    {
        $latestVersion = Yii::$app->cache->get('latestVersion');
        if ($latestVersion === false) {
            $info = self::request('v1/modules/getLatestVersion');

            if (isset($info['latestVersion'])) {
                $latestVersion = $info['latestVersion'];
            }

            Yii::$app->cache->set('latestVersion', $latestVersion);
        }

        return $latestVersion;
    }

}
