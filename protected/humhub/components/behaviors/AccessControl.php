<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * AccessControl provides a very basic controller access protection
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
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        $identity = Yii::$app->user->getIdentity();
        if($identity != null && !$identity->isActive()) {
            Yii::$app->user->logout();
            Yii::$app->response->redirect(['/user/auth/login']);
            return false;
        }
        
        if (Yii::$app->user->isGuest) {
            if (!$this->loggedInOnly && !$this->adminOnly) {
                return true;
            }
            if (in_array($action->id, $this->guestAllowedActions) && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') == 1) {
                return true;
            }
            if (!empty($this->rules) && !empty($this->guestAllowedActions)) {
                if (in_array($action->id, $this->guestAllowedActions)){
                    return true;
                }
            }
            Yii::$app->user->loginRequired();
            return false;
        }

        if ($this->adminOnly && !Yii::$app->user->isAdmin()) {
            $this->forbidden();
        }

        if (!empty($this->rules)) {
            $action = Yii::$app->controller->action->id;
            $userGroups = ArrayHelper::getColumn(ArrayHelper::toArray($identity->groups), 'name');
            $userGroups = array_map('strtolower', $userGroups);
            foreach ($this->rules as $rule){
                if (!empty($rule['groups'])){
                    $allowedGroups = array_map('strtolower', $rule['groups']);
                    foreach ($allowedGroups as $allowedGroup){
                        if(in_array($allowedGroup, $userGroups) && in_array($action, $rule['actions'])){
                            return true;
                        }
                    }
                }
            }
            $this->forbidden();
        }

        if ($this->loggedInOnly) {
            return true;
        }
       
        return false;
    }

    /**
     * @throws ForbiddenHttpException
     */
    protected function forbidden()
    {
        throw new ForbiddenHttpException(Yii::t('error', 'You are not allowed to perform this action.'));
    }

}
