<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 26.07.2017
 * Time: 13:33
 */

namespace humhub\components\access;

use humhub\libs\BasePermission;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\web\Controller;

/**
 * ControllerAccess contains the actual logic to verify if a user can access a given $action.
 *
 * By default the AccessCheck will set the current logged in user permission object, if $user is null, we assume a guest
 * user.
 *
 * The guest user access can be verified by calling the `reguiresLogin()` check.
 *
 * Inactive users are can be catched by calling `isInActiveUser()`.
 *
 * The actual permission rule verification is handled by the `verify()` check, subclasses may overwrite and extend this
 * function with additional checks.
 *
 * Subclasses can extend available validators by calling `registerValidator` and providing a validator setting array as:
 *
 * ```
 * public function init()
 * {
 *    parent::init();
 *    $this->registerValidator([
 *      self::RULE_MY_CUSTOM_RULE => 'validateCustomRule',
 *      'reason' => Yii::t('error', 'Guest mode not active, please login first.'),
 *      'code' => 401]);
 * }
 * ```
 *
 * The previous example registered an new validator repsonsible for validating $rules with name self::RULE_MY_CUSTOM_RULE and validation
 * handler function 'validateCustomRule' which defines an handler method within the subclass.
 *
 * The validator can be set the following additional settings:
 *
 *  - **reason**: Reason set if the validaiton fails
 *  - **code**: Http Code e.g. 404, 403, 401
 *  - **actionType**: Defines how to determine if a rule is action related by default an action rule allows the following action settings:
 * ['myCustomRule' => ['action1', 'action2']] and ['myCustomRule', 'actions' => ['action1', 'action2']] but can be restricted to the second definition only by setting ACTION_SETTING_TYPE_OPTION_ONLY
 * - **actionFilter**: if set to false the validations handler is always executed even if the action settings do not match with the current action (default true)
 * - **strict**: if set to false only one rule of a given validator has to pass otherwise all rules have to pass (default true)
 *
 * @since 1.2.2
 */
class ControllerAccess extends Object
{
    /**
     * Allows the action rule setting only by extra option ['myRule', 'actions' => ['action1', 'action2']]
     */
    const ACTION_SETTING_TYPE_OPTION_ONLY = 0;

    /**
     * Allows the action rule setting by extra option ['myRule', 'actions' => ['action1', 'action2']] or immediate ['myRule' => ['action1', 'action2']]
     */
    const ACTION_SETTING_TYPE_BOTH = 1;

    const RULE_ADMIN_ONLY = 'admin';
    const RULE_PERMISSION = 'permission';
    const RULE_LOGGED_IN_ONLY = 'login';
    const RULE_GUEST_CUSTOM = 'custom';
    const RULE_STRICT = 'strict';
    const RULE_DISABLED_USER = 'disabledUser';
    const RULE_UNAPPROVED_USER = 'unapprovedUser';
    const RULE_POST = 'post';
    const RULE_JSON = 'json';

    /**
     * @var array fixed rules will always be added to the current rule set
     */
    protected $fixedRules = [
        [self::RULE_DISABLED_USER],
        [self::RULE_UNAPPROVED_USER],
    ];

    /**
     * @var array defines all available validators, this list can be extended by calling `registerValidator()`
     */
    protected $validators = [];

    /**
     * @var User identity to test against
     */
    public $user;

    /**
     * @var string the controller action id to test
     */
    public $action;

    /**
     * @var array access rule array
     */
    protected $rules = [];

    /**
     * @var string actual decline message, can be changed in verify checks for specific error messages
     */
    public $reason;

    /**
     * @var int http code, can be changed in verify checks for specific error codes
     */
    public $code;

    /**
     * @var Controller owner object of this ControllerAccess the owner is mainly used to find custom validation handler
     */
    public $owner;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->user = Yii::$app->user->getIdentity();

        if (empty($this->action)) {
            $this->action = Yii::$app->controller->action->id;
        }

        $this->registerValidator([self::RULE_STRICT => 'validateStrictMode', 'reason' => Yii::t('error', 'Guest mode not active, please login first.'), 'code' => 401]);
        $this->registerValidator([self::RULE_UNAPPROVED_USER => 'validateUnapprovedUser', 'reason' => Yii::t('error', 'Your user account has not been approved yet, please try again later or contact a network administrator.'), 'code' => 401]);
        $this->registerValidator([self::RULE_DISABLED_USER => 'validateDisabledUser', 'reason' => Yii::t('error', 'Your user account is inactive, please login with an active account or contact a network administrator.'), 'code' => 401]);
        $this->registerValidator([self::RULE_LOGGED_IN_ONLY => 'validateLoggedInOnly', 'reason' => Yii::t('error', 'Login required for this section.'), 'code' => 401]);

        // We don't set code 401 since we want to show an error instead of redirecting to login
        $this->registerValidator(GuestAccessValidator::class);
        $this->registerValidator([self::RULE_ADMIN_ONLY => 'validateAdminOnly', 'reason' => Yii::t('error', 'You need admin permissions to access this section.')]);
        $this->registerValidator(PermissionAccessValidator::class);
        $this->registerValidator(DeprecatedPermissionAccessValidator::class);
        $this->registerValidator([self::RULE_POST => 'validatePostRequest', 'code' => 405, 'reason' => Yii::t('base', 'Invalid request method!')]);
        $this->registerValidator([self::RULE_JSON => 'validateJsonResponse']);
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules($rules = [])
    {
        $this->rules = array_merge($this->getFixedRules(), $rules);
    }

    /**
     * Adds a new validator to the available validators and sets some default values.
     *
     * A validator shoud have the following form
     *
     * `['ruleName' => 'handler', 'code' => 401, 'reason' => 'Some message in case the validation failed']`
     *
     * to allow other direct settings required by the action validator e.g. direct permission settings.
     *
     * @param $setting array validator setting array
     */
    protected function registerValidator($options)
    {
        if(is_string($options)) {
            $options = [
                'class' => $options,
            ];
        }

        $options['access'] = $this;

        $name = $this->getName($options);

        if($name == 'class') {
            $validator = Yii::createObject($options);
        } else if(class_exists($name)) {
            unset($options[0]);
            $options['class'] = $name;
            $validator = Yii::createObject($options);
        } else {
            $handler = $options[$name];
            unset($options[$name]);
            $options['name'] = $name;
            $options['owner'] = $this;
            $options['handler'] = $handler;
            $validator = new DelegateAccessValidator($options);
        }

        $this->validators[$validator->name] = $validator;
    }

    /**
     * Runs the current $rule setting against all available validators
     * @return bool
     */
    public function run()
    {
        $finished = [];

        foreach($this->rules as $rule) {
            $ruleName = $this->getName($rule);

            // A validator validates all rules of the given $name, so we don't have to rerun the validation here if already handled
            if(in_array($ruleName, $finished)) {
                continue;
            }

            $finished[] = $ruleName;

            $validator = $this->findValidator($ruleName);

            if(!$validator->run()) {
                $this->reason = (!$this->reason) ? $validator->getReason() : $this->reason;
                $this->code = (!$this->code) ? $validator->getCode(): $this->code;
                return false;
            }
        }

        return true;
    }

    protected function findValidator($ruleName)
    {
        if(isset($this->validators[$ruleName])) {
            return $this->validators[$ruleName];
        }

        return $this->getCustomValidator($ruleName);
    }

    protected function getCustomValidator($ruleName)
    {
        if($this->owner && method_exists($this->owner, $ruleName)) {
            return new DelegateAccessValidator([
                'access' => $this,
                'owner' => $this->owner,
                'handler' => $ruleName,
                'name' => $ruleName
            ]);
        }

        if (class_exists($ruleName)) {
            return Yii::createObject([
                'class' => $ruleName,
                'access' => $this
            ]);
        }

        throw new InvalidParamException('Invalid validator settings given for rule '.$ruleName);
    }

    /**
     * Extracts the ruleName from the given $array.
     *
     * @param $arr
     * @return mixed|null
     */
    protected function getName($arr)
    {
        if(empty($arr)) {
            return null;
        }

        $firstKey = current(array_keys($arr));
        if(is_string($firstKey)) {
            return $firstKey;
        } else {
            return $arr[$firstKey];
        }
    }

    /**
     * @return array returns array of rules which will always be added to the rule set
     */
    protected function getFixedRules()
    {
        return $this->fixedRules;
    }

    /**
     * @return bool makes sure if the current user is loggedIn
     */
    public function validateLoggedInOnly()
    {
        return !$this->isGuest();
    }

    /**
     * @return bool makes sure the current user has administration rights
     */
    public function validateAdminOnly()
    {
        return $this->isAdmin();
    }

    /**
     * @return bool checks if guest mode is activated for guestaccess
     */
    public function validateStrictMode()
    {
        return !$this->isGuest() || Yii::$app->user->isGuestAccessEnabled();
    }

    /**
     * @return mixed checks if the current request is a post request
     */
    public function validatePostRequest()
    {
        return Yii::$app->request->method == 'POST';
    }

    /**
     * @return bool makes sure the response type is json
     */
    public function validateJson()
    {
        Yii::$app->response->format = 'json';
        return true;
    }

    /**
     * @return bool checks if the current user is a disabled user
     */
    public function validateDisabledUser()
    {
        return $this->isGuest() || $this->user->status !== User::STATUS_DISABLED;
    }

    /**
     * @return bool checks if the current user is an unapproved user
     */
    public function validateUnapprovedUser()
    {
        return $this->isGuest() || $this->user->status !== User::STATUS_NEED_APPROVAL;
    }

    /**
     * @return bool Checks if the given $user is set.
     */
    public function isGuest()
    {
        return $this->user == null;
    }

    public function isAdmin()
    {
        return !$this->isGuest() && $this->user->isSystemAdmin();
    }
}