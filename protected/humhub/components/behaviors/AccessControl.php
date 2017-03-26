<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

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
     * Action ids which are allowed when Guest Mode is enabled
     *
     * @var array
     */
    public $guestAllowedActions = [];

    /**
     * Rules for access to controller
     *
     * @var array
     */
    public $rules = [];

    /**
     * Only allow admins access to this controller
     *
     * @var boolean
     */
    public $adminOnly = false;

    /**
     * Only allow logged in users access to this controller
     */
    public $loggedInOnly = true;

    /**
     * User groups cache;
     */
    private $_usergroupNames = null;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $identity = Yii::$app->user->getIdentity();

        if ($identity != null && !$identity->isActive()) {
            return $this->handleInactiveUser();
        }

        if (Yii::$app->user->isGuest && !$this->adminOnly) {
            return $this->handleGuestAccess($action);
        }

        if ($this->adminOnly && !Yii::$app->user->isAdmin()) {
            if ($this->getControllerSpace() == null || !$this->getControllerSpace()->isAdmin()) {
                $this->forbidden();
            }
        }

        if ($this->checkRules()) {
            return true;
        }

        return $this->loggedInOnly;
    }

    /**
     * Denys access for non active users by performing a logout.
     * @return boolean always false
     */
    protected function handleInactiveUser()
    {
        Yii::$app->user->logout();
        Yii::$app->response->redirect(['/user/auth/login']);

        return false;
    }

    /**
     * Checks access for guest users.
     *
     * Guests users are allowed to access an action if either the $loggedInOnly and $adminOnly flags are
     * set to false or the given controller action is contained in $guestAllowedActions.
     *
     * @return boolean
     */
    protected function handleGuestAccess($action)
    {
        if (!$this->loggedInOnly && !$this->adminOnly) {
            return true;
        }

        if (in_array($action->id, $this->guestAllowedActions) && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') == 1) {
            return true;
        }

        Yii::$app->user->loginRequired();

        return false;
    }

    /**
     * Checks group and permission rules.
     * @return boolean
     */
    protected function checkRules()
    {
        if (!empty($this->rules)) {
            foreach ($this->rules as $rule) {
                if ($this->checkGroupRule($rule) || $this->checkPermissionRule($rule)) {
                    return true;
                }
            }
            $this->forbidden();
        }

        return true;
    }

    /**
     * Checks permission rules.
     *
     * @param type $rule
     * @return boolean
     */
    protected function checkPermissionRule($rule)
    {
        if (!empty($rule['permissions'])) {
            if (!$this->checkRuleAction($rule)) {
                return false;
            }

            $permissionArr = (!is_array($rule['permissions'])) ? [$rule['permissions']] : $rule['permissions'];
            $params = isset($rule['params']) ? $rule['params'] : [];

            if ($this->isContentContainerController()) {
                return $this->owner->contentContainer->can($permissionArr, $params) || Yii::$app->user->can($permissionArr, $params);
            }

            return Yii::$app->user->can($permissionArr, $params);
        }

        return false;
    }

    protected function isContentContainerController()
    {
        return $this->owner instanceof \humhub\modules\content\components\ContentContainerController;
    }

    private function getControllerSpace()
    {
        if ($this->isContentContainerController()) {
            return $this->owner->getSpace();
        }

        return null;
    }

    /**
     * Checks the current controller action against the allowed rule action.
     * If the rule does not contain any action settings, the rule is allowed for all controller actions.
     *
     * @param array $rule
     * @return boolean true if current action is allowed
     */
    private function checkRuleAction($rule)
    {
        if (!empty($rule['actions'])) {
            $action = Yii::$app->controller->action->id;
            return in_array($action, $rule['actions']);
        }

        return true;
    }

    /**
     * Checks specific group access by group names.
     *
     * @param type $rule
     * @return boolean
     */
    protected function checkGroupRule($rule)
    {
        if (!empty($rule['groups'])) {
            $userGroups = $this->getUserGroupNames();
            $isAllowedAction = $this->checkRuleAction($rule);
            $allowedGroups = array_map('strtolower', $rule['groups']);
            foreach ($allowedGroups as $allowedGroup) {
                if (in_array($allowedGroup, $userGroups) && $isAllowedAction) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns an array of strings with all user groups of the current user.
     *
     * @return type
     */
    private function getUserGroupNames()
    {
        if ($this->_userGroupNames == null) {
            $identity = Yii::$app->user->getIdentity();
            $this->_userGroupNames = ArrayHelper::getColumn(ArrayHelper::toArray($identity->groups), 'name');
            $this->_userGroupNames = array_map('strtolower', $this->_userGroupNames);
        }

        return $this->_userGroupNames;
    }

    /**
     * @throws ForbiddenHttpException
     */
    protected function forbidden()
    {
        throw new ForbiddenHttpException(Yii::t('error', 'You are not allowed to perform this action.'));
    }

}
