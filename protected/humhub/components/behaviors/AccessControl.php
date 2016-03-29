<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use Yii;
use yii\web\ForbiddenHttpException;
use humhub\models\Setting;

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
            Yii::$app->response->redirect(Yii::$app->urlManager->createUrl('user/auth/login'));
        }

        if (Yii::$app->user->isGuest) {
            if (!$this->loggedInOnly && !$this->adminOnly) {
                return true;
            }
            if (in_array($action->id, $this->guestAllowedActions) && Setting::Get('allowGuestAccess', 'authentication_internal') == 1) {
                return true;
            }

            Yii::$app->user->loginRequired();
            return false;
        }
        if ($this->adminOnly && !Yii::$app->user->isAdmin()) {
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
