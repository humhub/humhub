<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\components\access;

use humhub\modules\user\helpers\AuthHelper;
use Yii;
use humhub\modules\user\models\User;
use yii\base\InvalidArgumentException;
use yii\base\BaseObject;
use yii\web\Controller;

/**
 * ControllerAccess contains the actual logic to verify whether or not a user can access a controller action by means of
 * a given set of access rules.
 *
 * By default the AccessCheck will use the current logged in user as permission subject.
 *
 * The actual permission rule verification is handled by the [[run()]] function.
 *
 * Subclasses can extend the set of available validators by calling [[registerValidator()]]
 * and providing a validator setting array as:
 *
 * ```php
 * public function init()
 * {
 *    parent::init();
 *    $this->registerValidator([
 *      self::RULE_MY_RULE => 'validateMyRule',
 *      'reason' => Yii::t('error', 'My validation rule could not be verified.'),
 *      'code' => 401
 *     ]);
 * }
 * ```
 *
 * The previous example registered a new validator responsible for validating rules with the name `validateMyRule`
 * and validation handler function `validateMyRule` which defines an handler method within the subclass.
 *
 * Custom Validators can also be added by means of a Validator class as in the following example:
 *
 * ```php
 * $this->registerValidator(MyValidator::class);
 * ```
 *
 * where `MyValidator` is a subclass of [[\humhub\components\access\AccessValidator]]
 *
 * A single rule is provided as a array. If not specified otherwise, a rule supports the following base format:
 *
 * ```php
 * ['ruleName', 'actions' => ['action1', 'action2']]
 * ```
 * or
 *
 * ```php
 * ['ruleName' => ['action1', action2]]
 * ```
 *
 * > Note: the second format is not supported by all rules e.g. permission rule
 *
 * If no action array is provided, the rule is considered to be controller global and will be verified for all actions.
 *
 * If a rule for a given name could not be found, the ControllerAccess tries to determine a custom rule validator
 * set by the controller itself:
 *
 * ```php
 * ['validateMyCustomRule', 'someParameter' => $value]
 * ```
 *
 * will search for controller validator function `validateMyCustomRule`:
 *
 * ```php
 * public function validateTestRule($rule, $access)
 * {
 *     if($rule['someParameter'] == 'valid') {
 *          $access->code = 401;
 *          $access->reason = 'Not authorized!';
 *          return false;
 *     }
 *
 *     return true;
 * }
 * ```
 *
 * By defining the [[fixedRules]] array property a ControllerAccess can define rules which are always applied,
 * this property (or [[getFixedRules()]] function may be overwritten by subclasses.
 *
 * The following rules are available by default:
 *
 *  - **admin**: The user has to be system admin to access a action
 *  - **permission** Group Permission check
 *  - **login**: The user has to be logged in to access a action
 *  - **strict**: Will check for guest users against the guest users allowed setting
 *  - **post**: Will only accept post requests for the given actions
 *  - **json**: Will handle json result requests by setting `Yii::$app->response->format = 'json'`
 *  - **ajax**: Allows only AJAX requests. See: `Yii::$app->request->isAjax`
 *  - **disabledUser**: Checks if the given user is a disabled user **(fixed)**
 *  - **unapprovedUser**: Checks if the given user is a unapproved user **(fixed)**
 *
 * @see AccessValidator
 * @since 1.2.2
 */
class ControllerAccess extends BaseObject
{
    /**
     * Allows the action rule setting only by extra option ['myRule', 'actions' => ['action1', 'action2']]
     */
    public const ACTION_SETTING_TYPE_OPTION_ONLY = 0;

    /**
     * Allows the action rule setting by extra option ['myRule', 'actions' => ['action1', 'action2']]
     * or immediate ['myRule' => ['action1', 'action2']]
     */
    public const ACTION_SETTING_TYPE_BOTH = 1;

    /**
     * Only admins have access to the given set of actions e.g.: ['admin' => ['action1']]
     */
    public const RULE_ADMIN_ONLY = 'admin';

    /**
     * Validate against a given set of permissions e.g.:
     * ['permission' => [MyPermission::class], 'actions' => ['action1']]
     */
    public const RULE_PERMISSION = 'permission';

    /**
     * Only logged in user have access  e.g.: ['login' => ['action1', 'action2']]
     */
    public const RULE_LOGGED_IN_ONLY = 'login';

    /**
     * Check guest mode  e.g.: ['strict'] (mainly used as global)
     */
    public const RULE_STRICT = 'strict';

    /**
     * Check guest if user is disabled
     */
    public const RULE_DISABLED_USER = 'disabledUser';

    /**
     * Check guest if user is unnapproved
     */
    public const RULE_UNAPPROVED_USER = 'unapprovedUser';

    /**
     * Check guest if user must change password
     * @since 1.8
     */
    public const RULE_MUST_CHANGE_PASSWORD = 'mustChangePassword';

    /**
     * Maintenance mode is active
     */
    public const RULE_MAINTENANCE_MODE = 'maintenance';

    /**
     * Check guest if request method is post
     */
    public const RULE_POST = 'post';

    /**
     * Make sure response type is json
     */
    public const RULE_JSON = 'json';

    /**
     * Only AJAX request is allowed for the actions
     */
    public const RULE_AJAX_ONLY = 'ajax';

    /**
     * @var array fixed rules will always be added to the current rule set
     */
    protected $fixedRules = [
        [self::RULE_DISABLED_USER],
        [self::RULE_UNAPPROVED_USER],
        [self::RULE_MUST_CHANGE_PASSWORD],
        [self::RULE_MAINTENANCE_MODE],
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
     * @var string Name of callback method to run after failed validation
     * @since 1.8
     */
    public $codeCallback;

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

        $this->registerValidator([
            self::RULE_STRICT => 'validateStrictMode',
            'reason' => Yii::t('error', 'Guest mode not active, please login first.'),
            'code' => 401,
        ]);
        $this->registerValidator([
            self::RULE_UNAPPROVED_USER => 'validateUnapprovedUser',
            'reason' => Yii::t('error', 'Your user account has not been approved yet, please try again later or contact a network administrator.'),
            'code' => 401,
        ]);
        $this->registerValidator([
            self::RULE_DISABLED_USER => 'validateDisabledUser',
            'reason' => Yii::t('error', 'Your user account is inactive, please login with an active account or contact a network administrator.'),
            'code' => 401,
        ]);
        $this->registerValidator([
            self::RULE_LOGGED_IN_ONLY => 'validateLoggedInOnly',
            'reason' => Yii::t('error', 'Login required for this section.'),
            'code' => 401,
        ]);
        $this->registerValidator([
            self::RULE_MAINTENANCE_MODE => 'validateMaintenanceMode',
            'reason' => ControllerAccess::getMaintenanceModeWarningText(),
            'code' => 403,
            'codeCallback' => 'checkMaintenanceMode',
        ]);
        $this->registerValidator([
            self::RULE_MUST_CHANGE_PASSWORD => 'validateMustChangePassword',
            'reason' => Yii::t('error', 'You must change password.'),
            'code' => 403,
            'codeCallback' => 'forceChangePassword',
        ]);

        // We don't set code 401 since we want to show an error instead of redirecting to login
        $this->registerValidator(GuestAccessValidator::class);
        $this->registerValidator([
            self::RULE_ADMIN_ONLY => 'validateAdminOnly',
            'reason' => Yii::t('error', 'You need admin permissions to access this section.'),
        ]);
        $this->registerValidator(PermissionAccessValidator::class);
        $this->registerValidator(DeprecatedPermissionAccessValidator::class);
        $this->registerValidator([
            self::RULE_POST => 'validatePostRequest',
            'reason' => Yii::t('base', 'Invalid request method!'),
            'code' => 405,
        ]);
        $this->registerValidator([self::RULE_JSON => 'validateJsonResponse']);
        $this->registerValidator([
            self::RULE_AJAX_ONLY => 'validateAjaxOnlyRequest',
            'reason' => Yii::t('error', 'The specified URL cannot be called directly.'),
            'code' => 405,
        ]);
    }

    /**
     * @return array set of rules
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Sets the current set of rules.
     * > Note: This will merge the given set of rules with the fixed rules.
     *
     * @param array $rules sets th
     */
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
     * @throws \yii\base\InvalidConfigException
     */
    protected function registerValidator($options)
    {
        if (is_string($options)) {
            $options = ['class' => $options];
        }

        $options['access'] = $this;

        $name = $this->getName($options);

        if ($name == 'class') {
            $validator = Yii::createObject($options);
        } elseif (class_exists($name)) {
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

        foreach ($this->rules as $rule) {
            $ruleName = $this->getName($rule);

            // A validator validates all rules of the given $name,
            // so we don't have to rerun the validation here if already handled
            if (in_array($ruleName, $finished)) {
                continue;
            }

            $finished[] = $ruleName;

            $validator = $this->findValidator($ruleName);

            if (!$validator->run()) {
                $this->reason = (!$this->reason) ? $validator->getReason() : $this->reason;
                $this->code = (!$this->code) ? $validator->getCode() : $this->code;
                if (isset($validator->codeCallback)) {
                    $this->codeCallback = $validator->codeCallback;
                }
                return false;
            }
        }

        return true;
    }

    protected function findValidator($ruleName)
    {
        if (isset($this->validators[$ruleName])) {
            return $this->validators[$ruleName];
        }

        return $this->getCustomValidator($ruleName);
    }

    protected function getCustomValidator($ruleName)
    {
        if ($this->owner && method_exists($this->owner, $ruleName)) {
            return new DelegateAccessValidator([
                'access' => $this,
                'owner' => $this->owner,
                'handler' => $ruleName,
                'name' => $ruleName,
            ]);
        }

        if (class_exists($ruleName)) {
            return Yii::createObject([
                'class' => $ruleName,
                'access' => $this,
            ]);
        }

        throw new InvalidArgumentException('Invalid validator settings given for rule ' . $ruleName);
    }

    /**
     * Extracts the ruleName from a given rule option array.
     *
     * @param $arr
     * @return mixed|null
     */
    protected function getName($arr)
    {
        if (empty($arr)) {
            return null;
        }

        $firstKey = current(array_keys($arr));
        if (is_string($firstKey)) {
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
        return !$this->isGuest() || AuthHelper::isGuestAccessEnabled();
    }

    /**
     * @return mixed checks if the current request is a post request
     */
    public function validatePostRequest()
    {
        return Yii::$app->request->isPost;
    }

    /**
     * @return mixed checks if the current request is an ajax request
     */
    public function validateAjaxOnlyRequest()
    {
        return Yii::$app->request->isAjax;
    }

    /**
     * @return bool makes sure the response type is json
     */
    public function validateJsonResponse()
    {
        Yii::$app->response->format = 'json';

        return true;
    }

    /**
     * @return bool checks if the current user is a disabled user
     */
    public function validateDisabledUser()
    {
        return $this->isGuest()
            || ($this->user->status !== User::STATUS_DISABLED
                && $this->user->status !== User::STATUS_SOFT_DELETED);
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

    /**
     * @return bool checks if the current user must change password
     * @since 1.8
     */
    public function validateMustChangePassword()
    {
        return $this->isGuest() || Yii::$app->user->isMustChangePasswordUrl() || !$this->user->mustChangePassword()
            || ($this->owner->module->id == 'user' && $this->owner->id == 'auth' && $this->owner->action->id == 'logout');
    }

    /**
     * @return bool makes sure the current user has an access on maintenance mode
     * @since 1.8
     */
    public function validateMaintenanceMode()
    {
        return !Yii::$app->settings->get('maintenanceMode')
            || $this->isAdmin()
            || ($this->owner->module->id == 'user' && $this->owner->id == 'auth' && in_array($this->owner->action->id, ['login', 'external']));
    }

    /**
     * @param string $beforeCustomInfo
     * @return string returns the maintenance mode warning text
     * @since 1.8
     */
    public static function getMaintenanceModeWarningText($beforeCustomInfo = ' ')
    {
        $customInfo = Yii::$app->settings->get('maintenanceModeInfo', '');

        return Yii::t('error', 'Maintenance mode is active. Only Administrators can access the platform.')
            . ($customInfo === '' ? '' : $beforeCustomInfo . $customInfo);
    }

}
