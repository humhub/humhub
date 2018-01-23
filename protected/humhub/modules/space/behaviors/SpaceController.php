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
namespace humhub\modules\space\behaviors;

use Yii;
use yii\base\Behavior;


use humhub\modules\space\models\Space;
use yii\web\HttpException;
use humhub\components\Controller;

/**
 * SpaceControllerBehavior is a controller behavior used for space modules/controllers.
 *
 * @author Luke
 * @package humhub.modules_core.space.behaviors
 * @since 0.6
 */
class SpaceController extends Behavior
{

    public $space = null;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    /**
     * Returns the current selected space by parameter guid
     *
     * If space doesnt exists or there a no permissions and exception
     * will thrown.
     *
     * @return Space
     * @throws HttpException
     */
    public function getSpace()
    {
        if ($this->space != null) {
            return $this->space;
        }

        // Get Space GUID by parameter
        $guid = Yii::$app->request->get('sguid');

        // Try Load the space
        $this->space = Space::findOne([
            'guid' => $guid
        ]);
        if ($this->space == null)
            throw new HttpException(404, Yii::t('SpaceModule.behaviors_SpaceControllerBehavior', 'Space not found!'));

        $this->checkAccess();
        return $this->space;
    }

    public function checkAccess()
    {
        if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') && Yii::$app->user->isGuest && $this->space->visibility != Space::VISIBILITY_ALL) {
            throw new HttpException(401, Yii::t('SpaceModule.behaviors_SpaceControllerBehavior', 'You need to login to view contents of this space!'));
        }

        // Save users last action on this space
        $membership = $this->space->getMembership(Yii::$app->user->id);
        if ($membership != null) {
            $membership->updateLastVisit();
        } else {

            // Super Admin can always enter
            if (! Yii::$app->user->isAdmin()) {
                // Space invisible?
                if ($this->space->visibility == Space::VISIBILITY_NONE) {
                    // Not Space Member
                    throw new HttpException(404, Yii::t('SpaceModule.behaviors_SpaceControllerBehavior', 'Space is invisible!'));
                }
            }
        }
    }

    public function beforeAction($action)
    {
        $this->owner->prependPageTitle($this->space->name);
    }
}

?>
