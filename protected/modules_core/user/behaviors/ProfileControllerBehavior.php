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

    public function getUser()
    {

        $guid = Yii::app()->request->getQuery('guid');

        if ($guid == "")
            $guid = Yii::app()->request->getQuery('uguid', Yii::app()->user->guid);


        $user = User::model()->findByAttributes(array('guid' => $guid));

        if ($user == null)
            throw new CHttpException(404, Yii::t('base', 'User not found!'));

        if ($user->status == User::STATUS_DELETED)
            throw new CHttpException(404, 'User deleted!');

        if ($user->status == User::STATUS_NEED_APPROVAL)
            throw new CHttpException(404, 'This user account is not approved yet!');

        return $user;
    }

}

?>
