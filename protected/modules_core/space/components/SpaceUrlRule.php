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
            
            /* Can we use a name instead? */
            $space = Space::model()->findByAttributes(array('guid' => $params['sguid']));
            if(strlen($space->name) == mb_strlen($space->name, 'utf-8') && !is_array((Space::model()->findByAttributes(array('name' => $space->name))))) {
                $params['sguid'] = $space->name;
            }
            
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
				
                /* Are we handling a GUID or Name? */
				$space = $this->isGuid($parts[1]) ? Space::model()->findByAttributes(array('guid' => $parts[1])) 
                    : Space::model()->findByAttributes(array('name' => urldecode(trim($parts[1]))));

	    		/* Not valid space or multiple occurrences. */
                if (!is_object($space)) {
                    throw new CHttpException('404', Yii::t('SpaceModule.components_SpaceUrlRule', 'Space not found!'));
                }
                   
                $_GET['sguid'] = $space->guid;
                
                if (!isset($parts[2]) || substr($parts[2], 0, 4) == 'home') {
                    return 'space/space/index';
                } else {
                    return $parts[2];
                }

            }
        }
        return false;
    }
	
	public static function isGuid($guid) {
		if (preg_match('/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i', $guid)) {
			return true;
		}
		return false;
	}

}
