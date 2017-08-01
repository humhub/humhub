<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Yii;
use yii\web\ForbiddenHttpException;
use humhub\components\access\ControllerAccess;
use yii\web\HttpException;

/**
 * AccessControl provides basic controller access protection
 *
 * Here are some examples access control settings:
 *
 * Allow guest access for action 'info'
 *
 * ```
 * [
 *      'acl' => [
 *          'class' => \humhub\components\behaviors\AccessControl::className(),
 *          'guestAllowedActions' => ['info']
 *      ]
 * ]
 * ```
 *
 * Allow access by pemission rule:
 *
 * ```
 * [
 *      'acl' => [
 *          'class' => \humhub\components\behaviors\AccessControl::className(),
 *          'rules' => [
 *              [
 *                  'groups' => [
 *                      'humhub\modules\xy\permissions\MyAccessPermssion'
 *                  ]
 *              ]
 *          ]
 *      ]
 * ]
 * ```
 *
 * @author luke
 */
class AccessControl extends \yii\base\ActionFilter
{

    /**
     * Rules for access to controller
     *
     * @var array
     */
    public $rules = [];

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
    protected $_controllerAccess;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Bypass when not installed for installer
        if (empty(Yii::$app->params['installed']) && Yii::$app->controller->module != null && Yii::$app->controller->module->id == 'installer') {
            return true;
        }

        $this->handleDeprecatedSettings();
        $this->_controllerAccess = $this->getControllerAccess($this->rules);

        if(!$this->_controllerAccess->run()) {
            if($this->_controllerAccess->code == 401) {
                return $this->loginRequired();
            } else {
                $this->forbidden();
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Compatibility with pre 1.2.2 usage of AccessControl
     */
    protected function handleDeprecatedSettings()
    {
        if($this->adminOnly) {
            $this->rules[] = [ControllerAccess::RULE_ADMIN_ONLY];
        }

        if($this->loggedInOnly) {
            $this->rules[] = [ControllerAccess::RULE_LOGGED_IN_ONLY];
        }

        if(!empty($this->guestAllowedActions)) {
            $this->rules[] = ['guestAccess' => $this->guestAllowedActions];
        }
    }

    /**
     * Returns a ControllerAccess instance, controllers are able to overwrite this by implementing an own `getAccess()`
     * function.
     *
     * @return ControllerAccess
     */
    protected function getControllerAccess($rules = [])
    {
        $instance = null;
        if(method_exists($this->owner, 'getAccess')) {
            $instance = $this->owner->getAccess();
        }

        if(!$instance) {
            $instance = new ControllerAccess();
        }

        $instance->setRules($rules);
        $instance->owner = $this->owner;

        return $instance;
    }


    /**
     * @throws ForbiddenHttpException
     */
    protected function forbidden()
    {
        throw new HttpException($this->_controllerAccess->code, $this->_controllerAccess->reason);
    }

    /**
     * @return bool forces user login
     */
    protected function loginRequired()
    {
        Yii::$app->user->logout();
        Yii::$app->user->loginRequired();
        return false;
    }
}
