<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use humhub\components\access\ControllerAccess;
use Yii;
use yii\base\ActionFilter;
use yii\web\HttpException;

/**
 * Handles the AccessControl for a Controller.
 *
 * Controller level AccessRules can be provided by either setting the [[rules]] array,
 * or by implementing a `getAccessRules()` function within the controller itself (prefered).
 *
 * **Examples:**
 *
 * Disable guest access for all controller actions:
 *
 * ```php
 * public function getAccessRules()
 * {
 *     return [
 *          ['login']
 *     ];
 * }
 * ```
 *
 * Disable guest access for specific controller actions:
 *
 * ```php
 * public function getAccessRules()
 * {
 *     return [
 *          ['login' => ['action1', 'action2']]
 *     ];
 * }
 * ```
 *
 * All users have to be logged in + additional permission check for 'action1' and 'action2':
 *
 * ```php
 * public function getAccessRules()
 * {
 *     return [
 *          ['login'],
 *          ['permission' => MyPermission::class, 'actions' => ['action1', 'action2']]
 *     ];
 * }
 * ```
 *
 * Custom inline validator for action 'action1':
 *
 * ```php
 * public function getAccessRules()
 * {
 *     return [
 *          ['validateMyCustomRule', 'someParameter' => 'someValue', 'actions' => ['action1']]
 *     ];
 * }
 *
 * public function validateMyCustomRule($rule, $access)
 * {
 *     if($rule['someParameter'] !== 'someValue') {
 *          $access->code = 401;
 *          $access->reason = 'Not authorized!';
 *          return false;
 *     }
 *
 *      return true;
 * }
 *
 * ```
 *
 * The list of available rules is given by the [[\humhub\components\access\ControllerAccess]] class set by a controller.
 * By default the base [[\humhub\components\access\ControllerAccess]] class will be used.
 *
 * The default ControllerAccess class can be overwritten by implementing the `getAccess()` function within a controller,
 * which should return an instance of ControllerAccess.
 *
 * > Note: You can also use the [[\humhub\components\Controller::access]] property
 * to define a ControllerAccess class string.
 *
 *
 *
 * @see ControllerAccess
 * @author luke
 */
class AccessControl extends ActionFilter
{

    /**
     * Rules for access to controller
     *
     * @var array
     */
    public $rules = null;

    /**
     * Action ids which are allowed when Guest Mode is enabled
     *
     * @var array
     * @deprecated since 1.2.2 use ['guestAccess' => ['action1', 'action2']] rule instead
     */
    public $guestAllowedActions = [];

    /**
     * Only allow admins access to this controller
     *
     * @var boolean
     * @deprecated since 1.2.2 use ['adminOnly'] rule instead
     */
    public $adminOnly = false;

    /**
     * Only allow logged in users access to this controller
     * @deprecated since 1.2.2 use ['loggedInOnly'] rule instead
     */
    public $loggedInOnly = false;

    /**
     * @var ControllerAccess instance
     */
    protected $controllerAccess;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Bypass when not installed for installer
        if (empty(Yii::$app->params['installed']) &&
                  Yii::$app->controller->module != null &&
                  Yii::$app->controller->module->id == 'installer') {
            return true;
        }

        $this->handleDeprecatedSettings();
        $this->controllerAccess = $this->getControllerAccess($this->rules);

        if (!$this->controllerAccess->run()) {
            if (isset($this->controllerAccess->codeCallback) &&
                method_exists($this, $this->controllerAccess->codeCallback)) {
                // Call a specific function for current action filter,
                // may be used to filter a logged in user for some restriction e.g. "must change password"
                call_user_func([$this, $this->controllerAccess->codeCallback]);
            } else if ($this->controllerAccess->code == 401) {
                $this->loginRequired();
            } else {
                $this->forbidden();
            }
            return false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Compatibility with pre 1.2.2 usage of AccessControl
     */
    protected function handleDeprecatedSettings()
    {
        if ($this->adminOnly) {
            $this->rules[] = [ControllerAccess::RULE_ADMIN_ONLY];
        }

        if ($this->loggedInOnly) {
            $this->rules[] = [ControllerAccess::RULE_LOGGED_IN_ONLY];
        }

        if (!empty($this->guestAllowedActions)) {
            $this->rules[] = ['guestAccess' => $this->guestAllowedActions];
        }
    }

    /**
     * Returns a ControllerAccess instance, controllers are able to overwrite this by implementing an own `getAccess()`
     * function.
     *
     * @return ControllerAccess
     */
    protected function getControllerAccess($rules = null)
    {
        if ($rules === null) {
            $rules = [['strict']];
        }

        $instance = null;
        if (method_exists($this->owner, 'getAccess')) {
            $instance = $this->owner->getAccess();
        }

        if (!$instance) {
            // fixes legacy behavior settings compatibility issue with no rules given
            $instance = new ControllerAccess();
        }

        $instance->setRules($rules);
        $instance->owner = $this->owner;

        return $instance;
    }

    /**
     * @throws HttpException
     */
    protected function forbidden()
    {
        throw new HttpException($this->controllerAccess->code, $this->controllerAccess->reason);
    }

    /**
     * Force user to log in
     */
    protected function loginRequired()
    {
        Yii::$app->user->logout();
        Yii::$app->user->loginRequired();
    }

    /**
     * Force user to redirect to change password
     * @since 1.8
     */
    protected function forceChangePassword()
    {
        if (!Yii::$app->user->isMustChangePasswordUrl()) {
            Yii::$app->getResponse()->redirect([Yii::$app->user->mustChangePasswordRoute]);
        }
    }

    /**
     * Log out all non admin users when maintenance mode is active
     * @since 1.8
     */
    protected function checkMaintenanceMode()
    {
        if (Yii::$app->settings->get('maintenanceMode')) {
            if (!Yii::$app->user->isGuest) {
                Yii::$app->user->logout();
                Yii::$app->getView()->warn(Yii::t('error', 'Maintenance mode activated: You have been automatically logged out and will no longer have access the platform until the maintenance has been completed.'));
            }
            Yii::$app->getResponse()->redirect(['/user/auth/login']);
        }
    }
}
