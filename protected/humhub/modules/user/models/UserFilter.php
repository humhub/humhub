<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use yii\helpers\Html;

/**
 * Special user model class for the purpose of searching users.
 *
 * @author Julian Harrer
 */
class UserFilter extends User
{

    /**
     * Returns a UserFilter instance for the given $user or the current user identity
     * if $user is not provided.
     * 
     * @param type $user
     * @return type
     */
    public static function forUser($user = null)
    {
        if ($user == null) {
            $user = Yii::$app->user->getIdentity();
        }

        $userId = ($user instanceof User) ? $user->id : $user;
        return self::findIdentity($userId);
    }

    /**
     * Returns a json representation for a userpicker search. 
     * 
     * @param type $keywords
     * @param type $maxResults
     * @param type $friendsOnly
     * @param type $permission
     * @return type
     */
    public function getUserPickerResult($keywords = null, $maxResults = null, $friendsOnly = false, $permission = null)
    {
        if (!Yii::$app->getModule('friendship')->getIsEnabled()) {
            //We don't use the permission here for filtering since include user with no permission as disabled in the result.
            //The problem here is we do not prefer users with permission in the query.
            $users = $this->getUserByFilter($keywords, $maxResults);
            return self::asJSON($users, $permission);
        }

        $friends = $this->getFriendsByFilter($keywords, $maxResults);
        
        //Create userinfo json with with set 'disabled' field if the user is not permitted
        $jsonResult = self::asJSON($friends, $permission);
        
        //Fill the remaining space with member users with permission
        if (!$friendsOnly && count($friends) < $maxResults) {
            $additionalUser = [];
            //Here we filter with permission since we don't want to have non friend user without the permission in the result
            foreach($this->getUserByFilter($keywords, ($maxResults - count($friends)), $permission) as $user) {
                if(!$this->containsUser($friends, $user)) {
                    $additionalUser[] = $user;
                }
            }
            $jsonResult = array_merge($jsonResult, self::asJSON($additionalUser));
        }

        return $jsonResult;
    }
    
    private function containsUser($userArr, $user)
    {
        foreach($userArr as $currentUser) {
            if($currentUser->id === $user->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Searches for all active users by the given keyword and permission.
     * 
     * @param type $keywords
     * @param type $maxResults
     * @param type $permission
     * @return type
     */
    public function getUserByFilter($keywords = null, $maxResults = null, $permission = null)
    {
        return self::filter(User::find(), $keywords, $maxResults, $permission);
    }

    /**
     * Search for all active friends by the given keyword and permission
     * 
     * @param type $keywords search keyword
     * @param type $maxResults
     * @param type $permission
     * @return type
     */
    public function getFriendsByFilter($keywords = null, $maxResults = null, $permission = null)
    {
        return self::filter($this->getFriends(), $keywords, $maxResults, $permission);
    }
    
    public static function filter($query, $keywords = null, $maxResults = null, $permission = null, $active = null)
    {
        $user = self::addQueryFilter($query, $keywords, $maxResults, $active)->all();
        return self::filterByPermission($user, $permission);
    }

    public static function addQueryFilter($query, $keywords = null, $maxResults = null, $active = null)
    {
        self::addKeywordFilter($query, $keywords);
        
        if ($maxResults != null) {
            $query->limit($maxResults);
        }
        
        //We filter active user by default
        if(($active != null && $active) || $active == null) {
            $query->active();
        }
        
        return $query;
    }
    
    public static function addKeywordFilter($query, $keyword)
    {
        $query->joinWith('profile');
        $parts = explode(" ", $keyword);
        foreach ($parts as $part) {
            /*$query->andWhere("(user.email LIKE :match OR "
                    . "user.username LIKE :match OR "
                    . "profile.firstname LIKE :match OR "
                    . "profile.lastname LIKE :match OR "
                    . "profile.title LIKE :match)", ['match' => '%' . $part . '%']);*/
            $query->andFilterWhere(
                    ['or',
                        ['like', 'user.email', $part],
                        ['like', 'user.username', $part],
                        ['like', 'profile.firstname', $part],
                        ['like', 'profile.lastname', $part],
                        ['like', 'profile.title', $part]
                    ]
            );
        }
    }

    /**
     * Returns a subset of the given array containing all users of the given set
     * which are permitted. If the permission is null this method returns the
     * 
     * @param type $users
     * @param type $permission
     * @return type
     */
    public static function filterByPermission($users, $permission)
    {
        if ($permission === null) {
            return $users;
        }

        $result = [];

        foreach ($users as $user) {
            if ($user->getPermissionManager()->can($permission)) {
                $result[] = $user;
            }
        }

        return $result;
    }

    /**
     * Creates an json result with user information arrays. A user will be marked
     * as disabled, if the permission check fails on this user.
     * 
     * @param type $users
     * @param type $permission
     * @return type
     */
    public static function asJSON($users, $permission = null)
    {
        if (is_array($users)) {
            $result = [];
            foreach ($users as $user) {
                if ($user != null) {
                    $result[] = self::createJSONUserInfo($user, $permission);
                }
            }
            return $result;
        } else {
            return self::createJsonUserInfo($users, $permission);
        }
    }

    /**
     * Creates an single user-information array for a given user. A user will be marked
     * as disabled, if the permission check fails on this user.
     * 
     * @param type $user
     * @param type $permission
     * @return type
     */
    private static function createJSONUserInfo($user, $permission = null)
    {
        $disabled = false;
        
        if($permission != null && $permission instanceof \humhub\libs\BasePermission) {
            $disabled = !$user->getPermissionManager()->can($permission);
        } else if($permission != null) {
            $disabled = $permission;
        }
        
        $userInfo = [];
        $userInfo['guid'] = $user->guid;
        $userInfo['disabled'] = $disabled;
        $userInfo['displayName'] = Html::encode($user->displayName);
        $userInfo['image'] = $user->getProfileImage()->getUrl();
        $userInfo['link'] = $user->getUrl();
        return $userInfo;
    }

}
