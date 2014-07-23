<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * SpaceUrlRule creates /s/spaceGuid style urls.
 *
 * @package humhub.modules_core.space.components
 * @since 0.6
 */
class SpaceUrlRule extends CBaseUrlRule
{

    public $connectionId = 'db';

    public function createUrl($manager, $route, $params, $ampersand)
    {

        if (isset($params['sguid'])) {
            if ($route == 'space/space' || $route == 'space/space/index') {
                $route = "home";
            }
            $url = "s/" . urlencode($params['sguid']) . "/" . $route;
            unset($params['sguid']);
            $url = rtrim($url . '/' . $manager->createPathInfo($params, '/', '/'), '/');
            return $url;
        }

        return false;
    }

    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        if (substr($pathInfo, 0, 2) == "s/") {
            $parts = explode('/', $pathInfo, 3);
            if (isset($parts[1])) {
                $space = Space::model()->findByAttributes(array('guid' => $parts[1]));

                if ($space !== null) {
                    
                    $_GET['sguid'] = $space->guid;
                    if (!isset($parts[2]) || substr($parts[2], 0, 4) == 'home') {
                        $temp = 1;
                        return 'space/space/index'. str_replace('home', '', $parts[2], $temp);
                    } else {
                        return $parts[2];
                    }
                } else {
                    throw new CHttpException('404', Yii::t('SpaceModule.components_SpaceUrlRule', 'Space not found!'));
                }
            }
        }
        return false;
    }

}
