<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;
use humhub\modules\user\models\User;

/**
 * User Profile URL Rule
 *
 * @author luke
 */
class UrlRule extends Object implements UrlRuleInterface
{

    /**
     * @var array cache map with user guid/username pairs
     */
    protected static $userUrlMap = [];

    /**
     * @var string default route to space home
     */
    public $defaultRoute = 'user/profile';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if (isset($params['uguid'])) {
            $username = static::getUrlByUserGuid($params['uguid']);
            if ($username !== null) {
                unset($params['uguid']);

                if ($this->defaultRoute == $route) {
                    $route = "";
                }

                $url = "u/" . urlencode(strtolower($username)) . "/" . $route;
                if (!empty($params) && ($query = http_build_query($params)) !== '') {
                    $url .= '?' . $query;
                }
                return $url;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (substr($pathInfo, 0, 2) == "u/") {
            $parts = explode('/', $pathInfo, 3);
            if (isset($parts[1])) {
                $user = User::find()->where(['username' => $parts[1]])->one();
                if ($user !== null) {
                    if (!isset($parts[2]) || $parts[2] == "") {
                        $parts[2] = $this->defaultRoute;
                    }
                    $params = $request->get();
                    $params['uguid'] = $user->guid;

                    return [$parts[2], $params];
                }
            }
        }
        return false;
    }

    /**
     * Gets usernameby given guid
     * 
     * @param string $guid
     * @return string|null the username
     */
    public static function getUrlByUserGuid($guid)
    {
        if (isset(static::$userUrlMap[$guid])) {
            return static::$userUrlMap[$guid];
        }

        $user = User::findOne(['guid' => $guid]);
        if ($user !== null) {
            static::$userUrlMap[$user->guid] = $user->username;
            return static::$userUrlMap[$user->guid];
        }

        return null;
    }

}
