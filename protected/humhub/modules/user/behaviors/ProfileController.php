<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\user\behaviors;

use Yii;
use yii\base\Behavior;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\components\Controller;

/**
 * This Behavior needs to be attached to all controllers which are provides
 * modules to the Profile System.
 *
 * @package humhub.modules_core.user
 * @since 0.5
 * @author Luke
 */
class ProfileController extends Behavior
{

    public $user = null;

    public function events() {

        return [
        Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function getUser()
    {
        if ($this->user != null) {
            return $this->user;
        }

        $guid = Yii::$app->request->getQuery('uguid');
        $this->user = User::findOne(['guid' => $guid]);

        if ($this->user == null)
            throw new HttpException(404, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'User not found!'));

        $this->checkAccess();

        return $this->user;
    }

    public function checkAccess()
    {
        if ($this->user->status == User::STATUS_NEED_APPROVAL) {
            throw new HttpException(404, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'This user account is not approved yet!'));
        }
        if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') && $this->user->visibility != User::VISIBILITY_ALL && Yii::$app->user->isGuest) {
            throw new HttpException(401, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'You need to login to view this user profile!'));
        }
    }

    public function beforeAction($action) {

        $this->owner->prependPageTitle($this->user->displayName);
    }

}

?>
