<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\modules\user\components\ActiveQueryUser;
use Yii;

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
     * Returns an array of user models filtered by a $keyword and $permission. These filters
     * are added to the provided $query. The $keyword filter can be used to filter the users
     * by email, username, firstname, lastname and title. By default this functions does not
     * consider inactive user.
     *
     * @param type $query
     * @param type $keywords
     * @param type $maxResults
     * @param type $permission
     * @param type $active
     * @return type
     */
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
        if (($active != null && $active) || $active == null) {
            $query->active();
        }

        return $query;
    }

    /**
     * Filter users by keyword
     *
     * @param ActiveQueryUser $query
     * @param string|array $keyword
     * @return ActiveQueryUser
     */
    public static function addKeywordFilter($query, $keyword)
    {
        return $query->search($keyword);
    }

    /**
     * Returns a subset of the given array containing all users of the given set
     * which are permitted. If the permission is null this method returns the
     *
     * @param $users
     * @param $permission
     * @return array
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
}
