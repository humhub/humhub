<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\behaviors;

use Yii;
use yii\base\Behavior;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\components\Controller;

/**
 * ProfileController Behavior
 * 
 * In User container scopes, this behavior will automatically attached to a contentcontainer controller.
 * 
 * @see User::controllerBehavior
 * @see \humhub\modules\contentcontainer\components\Controller
 * @property \humhub\modules\contentcontainer\components\Controller $owner the controller
 */
class ProfileController extends Behavior
{

    /**
     * @var User the user
     */
    public $user = null;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (!$this->owner->contentContainer instanceof User) {
            throw new \yii\base\InvalidValueException('Invalid contentcontainer type of controller.');
        }

        $this->user = $this->owner->contentContainer;
    }

    /**
     * 
     * @return type
     */
    public function getUser()
    {
        return $this->user;
    }

    public function beforeAction($action)
    {
        if ($this->user->status == User::STATUS_NEED_APPROVAL) {
            throw new HttpException(404, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'This user account is not approved yet!'));
        }
        if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') && $this->user->visibility != User::VISIBILITY_ALL && Yii::$app->user->isGuest) {
            throw new HttpException(401, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'You need to login to view this user profile!'));
        }

        $this->owner->prependPageTitle($this->user->displayName);
        $this->owner->subLayout = "@humhub/modules/user/views/profile/_layout";
    }

}

?>
