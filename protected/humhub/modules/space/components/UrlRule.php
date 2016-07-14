<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use yii\helpers\Url;
use yii\web\UrlRuleInterface;
use yii\base\Object;
use humhub\modules\space\models\Space;

/**
 * Space URL Rule
 *
 * @author luke
 */
class UrlRule extends Object implements UrlRuleInterface
{

    /**
     * @var string default route to space home
     */
    public $defaultRoute = 'space/space';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if (isset($params['sguid'])) {
            if ($route == $this->defaultRoute) {
                $route = '';
            }
            $url = "s/" . urlencode($params['sguid']) . "/" . $route;
            unset($params['sguid']);

            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '?' . $query;
            }
            return $url;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (substr($pathInfo, 0, 2) == "s/") {
            $parts = explode('/', $pathInfo, 3);
            if (isset($parts[1])) {
                $space = Space::find()->where(['guid' => $parts[1]])->one();
                if ($space !== null) {
                    if (!isset($parts[2]) || $parts[2] == "") {
                        $parts[2] = $this->defaultRoute;
                    }

                    $params = $request->get();
                    $params['sguid'] = $space->guid;

                    return [$parts[2], $params];
                }
            }
        }
        return false;
    }

}
