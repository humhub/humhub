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

/**
 * This Behavior needs to be attached to all controllers which are provides
 * modules to the Profile System.
 *
 * @package humhub.modules_core.user
 * @since 0.5
 * @author Luke
 */
class ProfileControllerBehavior extends CBehavior
{

    public $user = null;

    public function getUser()
    {

        if ($this->user != null) {
            return $this->user;
        }

        // Get User GUID by parameter
        $guid = Yii::app()->request->getQuery('uguid');
        if ($guid == "") {
            // Workaround for older version
            $guid = Yii::app()->request->getQuery('guid');
        }

        $this->user = User::model()->findByAttributes(array('guid' => $guid));

        if ($this->user == null)
            throw new CHttpException(404, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'User not found!'));

        $this->checkAccess();

        return $this->user;
    }

    public function checkAccess()
    {
        if ($this->user->status == User::STATUS_NEED_APPROVAL) {
            throw new CHttpException(404, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'This user account is not approved yet!'));
        }
        if ($this->user->visibility != User::VISIBILITY_ALL && Yii::app()->user->isGuest) {
            throw new CHttpException(401, Yii::t('UserModule.behaviors_ProfileControllerBehavior', 'You need to login to view this user profile!'));
        }
    }

    /**
     * Creates a user url
     *
     * @deprecated since version 0.9
     * @param type $route
     * @param type $params
     * @param type $ampersand
     * @return type
     */
    public function createUserUrl($route, $params = array(), $ampersand = '&')
    {
        return $this->user->createUrl($route, $params, $ampersand);
    }

}

?>
