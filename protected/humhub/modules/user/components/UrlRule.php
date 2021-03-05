<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;
use humhub\modules\user\models\User as UserModel;

/**
 * User Profile URL Rule
 *
 * @author luke
 */
class UrlRule extends BaseObject implements UrlRuleInterface
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
        if (isset($params['cguid'])) {
            $username = static::getUrlByUserGuid($params['cguid']);
            if ($username !== null) {
                unset($params['cguid']);

                if ($this->defaultRoute == $route) {
                    $route = "";
                }

                $url = urlencode($username) . "/" . $route;
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

            $parts = explode('/', $pathInfo, 2);
            if (isset($parts[0])) {
                $user = UserModel::find()->where(['username' => $parts[0]])->one();
                if ($user !== null) {
                    if (!isset($parts[1]) || $parts[1] == "") {
                        $parts[1] = $this->defaultRoute;
                    }
                    $params = $request->get();
                    $params['cguid'] = $user->guid;

                    return [$parts[1], $params];
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

        $user = UserModel::findOne(['guid' => $guid]);
        static::$userUrlMap[$guid] = ($user !== null) ? $user->username : null;
        return static::$userUrlMap[$guid];
    }

}
