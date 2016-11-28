<?php

namespace humhub\modules\user\models;

use Yii;
use yii\helpers\Html;
use humhub\modules\user\models\UserFilter;

/**
 * This class can be used to filter results for a user picker search query by calling the static
 * filter method.
 *
 * @since 1.2
 * @author buddha
 */
class UserPicker
{
    
    /**
     * Creates a json user array used in the userpicker js frontend.
     * The $cfg is used to specify the filter values the following values are available:
     * 
     * query - (ActiveQuery) The initial query which is used to append additional filters. - default = User Friends if friendship module is enabled else User::find()
     * 
     * active - (boolean) Specifies if only active user should be included in the result - default = true
     * 
     * maxResults - (int) The max number of entries returned in the array - default = 10
     * 
     * keyword - (string) A keyword which filters user by username, firstname, lastname, email and title
     * 
     * permission - (BasePermission) An additional permission filter
     * 
     * fillQuery - (ActiveQuery) Can be used to fill the result array if the initial query does not return the maxResults, these results will have a lower priority
     * 
     * fillUser - (boolean) When set to true and no fillQuery is given the result is filled with User::find() results
     * 
     * disableFillUser - Specifies if the results of the fillQuery should be disabled in the userpicker results - default = true
     * 
     * @param type $cfg filter configuration
     * @return type json representation used by the userpicker
     */
    public static function filter($cfg = null)
    {
        $defaultCfg = [
            'active' => true,
            'maxResult' => 10,
            'disableFillUser' => true,
            'keyword' => null,
            'permission' => null,
            'fillQuery' => null,
            'disabledText' => null,
            'fillUser' => false,
            'filter' => null
        ];
        
        $cfg = ($cfg == null) ? $defaultCfg : array_merge($defaultCfg, $cfg);
        
        //If no initial query is given we use getFriends if friendship module is enabled otherwise all users
        if(!isset($cfg['query'])) {
            $cfg['query'] = (Yii::$app->getModule('friendship')->getIsEnabled()) 
                    ? Yii::$app->user->getIdentity()->getFriends()
                    : UserFilter::find();
        }
        
        //Filter the initial query and disable user without the given permission
        $user = UserFilter::filter($cfg['query'], $cfg['keyword'], $cfg['maxResult'], null, $cfg['active']);
        $jsonResult = self::asJSON($user, $cfg['permission'], 2, $cfg['disabledText']);
        
        //Fill the result with additional users if it's allowed and the result count less than maxResult
        if(count($user) < $cfg['maxResult'] && (isset($cfg['fillQuery']) || $cfg['fillUser']) ) {
            
            //Filter out users by means of the fillQuery or default the fillQuery
            $fillQuery = (isset($cfg['fillQuery'])) ? $cfg['fillQuery'] : UserFilter::find();
            UserFilter::addKeywordFilter($fillQuery, $cfg['keyword'], ($cfg['maxResult'] - count($user)));
            $fillQuery->andFilterWhere(['not in', 'id', self::getUserIdArray($user)]);
            $fillUser = $fillQuery->all();
            
            //Either the additional users are disabled (by default) or we disable them by permission
            $disableCondition = (isset($cfg['permission'])) ? $cfg['permission']  : $cfg['disableFillUser'];
            $jsonResult = array_merge($jsonResult, self::asJSON($fillUser, $disableCondition, 1, $cfg['disabledText']));
        }   
        
        if($cfg['filter'] != null) {
            array_walk($jsonResult, $cfg['filter']);
        }
        
        return $jsonResult;
    }
    
    /**
     * Assambles all user Ids of the given $users into an array
     * 
     * @param array $users array of user models
     * @return array user id array
     */
    private static function getUserIdArray($users)
    {
        $result = [];
        foreach($users as $user) {
            $result[] = $user->id;
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
    public static function asJSON($users, $permission = null, $priority = null, $disabledText = null)
    {
        if (is_array($users)) {
            $result = [];
            foreach ($users as $user) {
                if ($user != null) {
                    $result[] = self::createJSONUserInfo($user, $permission, $priority, $disabledText);
                }
            }
            return $result;
        } else {
            return self::createJSONUserInfo($users, $permission, $priority, $disabledText);
        }
    }

    /**
     * Creates a single user-information array for the given $user. A user will be marked
     * as disabled, if the given $permission check fails on this user. If the second argument
     * is of type boolean, the it will define the disabled field of the result directly.
     * 
     * @param type $user
     * @param \humhub\libs\BasePermission|boolean|null if boolean is given
     * @return type
     */
    private static function createJSONUserInfo($user, $permission = null, $priority = null, $disabledText = null)
    {
        $disabled = false;
        
        if($permission != null && $permission instanceof \humhub\libs\BasePermission) {
            $disabled = !$user->getPermissionManager()->can($permission);
        } else if($permission != null) {
            $disabled = $permission;
        }

        return [
            'id' => $user->id,
            'guid' => $user->guid,
            'disabled' => $disabled,
            'disabledText' => ($disabled) ? $disabledText : null,
            'text' => Html::encode($user->displayName),
            'image' => $user->getProfileImage()->getUrl(),
            'priority' => ($priority == null) ? 0 : $priority,
            'link' => $user->getUrl()
        ];
    }
}