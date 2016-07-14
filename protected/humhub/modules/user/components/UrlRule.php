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
     * @var string default route to space home
     */
    public $defaultRoute = 'user/profile';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if (isset($params['uguid'])) {
            $user = User::find()->where(['guid' => $params['uguid']])->one();
            if ($user !== null) {
                unset($params['uguid']);

                if ($this->defaultRoute == $route) {
                    $route = "";
                }

                $url = "u/" . urlencode(strtolower($user->username)) . "/" . $route;
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

}
