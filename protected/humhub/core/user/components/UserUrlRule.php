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
 * UserUrlRule creates /u/userGuid style urls.
 *
 * @package humhub.modules_core.user.components
 * @since 0.6
 */
class UserUrlRule extends CBaseUrlRule
{

    public $connectionId = 'db';

    /**
     * Store already looked up usernames
     * 
     * @var Array
     */
    private static $loadedUserNamesByGuid = array();

    public function createUrl($manager, $route, $params, $ampersand)
    {

        if (isset($params['uguid'])) {

            $userName = self::getUserNameByGuid($params['uguid']);

            unset($params['uguid']);
            if ($route == 'user/profile' || $route == 'user/profile/index') {
                $route = "home";
            }

            $url = "u/" . urlencode(strtolower($userName)) . "/" . $route;
            $url = rtrim($url . '/' . $manager->createPathInfo($params, '/', '/'), '/');
            return $url;
        }

        return false;
    }

    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        if (substr($pathInfo, 0, 2) == "u/") {
            $parts = explode('/', $pathInfo, 3);
            if (isset($parts[1])) {

                $user = User::model()->findByAttributes(array('username' => $parts[1]));
                /* Set default page */
                if (!isset($parts[2])) {
                    $parts[2] = 'home'; 
                }
                if ($user !== null) {
                    $_GET['uguid'] = $user->guid;
                    if (substr($parts[2], 0, 4) == 'home') {
                        $temp = 1;
                        return 'user/profile/index'. str_replace('home', '', $parts[2], $temp);
                    } else {
                        return $parts[2];
                    }                    
                }
            }
        }
        return false;
    }

    /**
     * Looks up username by given user guid
     * 
     * @param String $guid of user
     * @return Username
     * @throws CException when user not found
     */
    public static function getUserNameByGuid($guid)
    {

        if (isset(self::$loadedUserNamesByGuid[$guid])) {
            return self::$loadedUserNamesByGuid[$guid];
        }

        $user = User::model()->resetScope()->findByAttributes(array('guid' => $guid));
        
        if ($user != null) {
            self::$loadedUserNamesByGuid[$guid] = $user->username;
            return self::$loadedUserNamesByGuid[$guid];
        } else {
            throw new CException("Could not find user by uguid!");
        }

        return "";
    }

}
