<?php

namespace humhub\modules\user\widgets;

use Yii;
use yii\helpers\Html;
use \yii\helpers\Url;
use humhub\modules\user\models\UserFilter;

/**
 * UserPickerWidget displays a user picker instead of an input field.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('application.modules_core.user.widgets.UserPickerWidget',array(
 *     'name'=>'users',
 *
 *     // additional javascript options for the date picker plugin
 *     'options'=>array(
 *         'showAnim'=>'fold',
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;'
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the userpicker plugin. Please refer to
 * the documentation for possible options (name-value pairs).
 *
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class UserPicker extends \yii\base\Widget
{

    /**
     * Id of input element which should replaced
     *
     * @var type
     */
    public $inputId = "";

    /**
     * JSON Search URL - defaults: search/json
     *
     * The token -keywordPlaceholder- will replaced by the current search query.
     *
     * @var String Url with -keywordPlaceholder-
     */
    public $userSearchUrl = "";

    /**
     * Maximum users
     *
     * @var type
     */
    public $maxUsers = 50;

    /**
     * Set guid for the current user
     *
     * @var type string
     */
    public $userGuid = "";

    /**
     * Set focus to input or not
     *
     * @var type boolean
     */
    public $focus = false;

    /**
     * @var CModel the data model associated with this widget.
     */
    public $model = null;

    /**
     * @var string the attribute associated with this widget.
     * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
     */
    public $attribute = null;

    /**
     * @var string for input placeholder attribute.
     */
    public $placeholderText = "";
    
    /**
     * Can be used to filter user roles like friends only
     * @var type 
     */
    public $userRole = null;
    
    /**
     * Used to transfer additional data to the server
     * @var type 
     */
    public $data = null;

    /**
     * Inits the User Picker
     *
     */
    public function init()
    {
        // Default user search for all users
        if ($this->userSearchUrl == "") {
            // provide the space id if the widget is calling from a space
            if (Yii::$app->controller->id == 'space') {
                $spaceId = Yii::$app->controller->getSpace()->id;
                $this->userSearchUrl = Url::toRoute(['/user/search/json', 'keyword' => '-keywordPlaceholder-', 'space_id' => $spaceId]);
            } else {
                $this->userSearchUrl = Url::toRoute(['/user/search/json', 'keyword' => '-keywordPlaceholder-']);
            }
        }
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {

        // Try to get current field value, when model & attribute attributes are specified.
        $currentValue = "";
        if ($this->model != null && $this->attribute != null) {
            $attribute = $this->attribute;
            $currentValue = $this->model->$attribute;
        }

        return $this->render('userPicker', [
                    'userSearchUrl' => $this->userSearchUrl,
                    'maxUsers' => $this->maxUsers,
                    'currentValue' => $currentValue,
                    'inputId' => $this->inputId,
                    'focus' => $this->focus,
                    'userGuid' => $this->userGuid,
                    'userRole' => $this->userRole,
                    'data' => json_encode($this->data),
                    'placeholderText' => $this->placeholderText,
        ]);
    }
    
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
            'fillUser' => false
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
        $jsonResult = self::asJSON($user, $cfg['permission'], 2);
        
        //Fill the result with additional users if it's allowed and the result count less than maxResult
        if(count($user) < $cfg['maxResult'] && (isset($cfg['fillQuery']) || $cfg['fillUser']) ) {
            
            //Filter out users by means of the fillQuery or default the fillQuery
            $fillQuery = (isset($cfg['fillQuery'])) ? $cfg['fillQuery'] : UserFilter::find();
            UserFilter::addKeywordFilter($fillQuery, $cfg['keyword'], ($cfg['maxResult'] - count($user)));
            $fillQuery->andFilterWhere(['not in', 'id', self::getUserIdArray($user)]);
            $fillUser = $fillQuery->all();
            
            //Either the additional users are disabled (by default) or we disable them by permission
            $disableCondition = (isset($cfg['permission'])) ? $cfg['permission']  : $cfg['disableFillUser'];
            $jsonResult = array_merge($jsonResult, UserPicker::asJSON($fillUser, $disableCondition, 1));
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
    public static function asJSON($users, $permission = null, $priority = null)
    {
        if (is_array($users)) {
            $result = [];
            foreach ($users as $user) {
                if ($user != null) {
                    $result[] = self::createJSONUserInfo($user, $permission, $priority);
                }
            }
            return $result;
        } else {
            return self::createJsonUserInfo($users, $permission, $priority);
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
    private static function createJSONUserInfo($user, $permission = null, $priority = null)
    {
        $disabled = false;
        
        if($permission != null && $permission instanceof \humhub\libs\BasePermission) {
            $disabled = !$user->getPermissionManager()->can($permission);
        } else if($permission != null) {
            $disabled = $permission;
        }
        
        $priority = ($priority == null) ? 0 : $priority;
        
        $userInfo = [];
        $userInfo['id'] = $user->id;
        $userInfo['guid'] = $user->guid;
        $userInfo['disabled'] = $disabled;
        $userInfo['displayName'] = Html::encode($user->displayName);
        $userInfo['image'] = $user->getProfileImage()->getUrl();
        $userInfo['priority'] = $priority;
        $userInfo['link'] = $user->getUrl();
        return $userInfo;
    }
}

?>
