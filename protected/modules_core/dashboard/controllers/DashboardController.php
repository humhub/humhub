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
 * Dashboard Controller
 *
 * @package humhub.controllers
 * @since 0.5
 */
class DashboardController extends Controller
{

    public $contentOnly = true;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'users' => array('@', (HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actions()
    {
        return array(
            'stream' => array(
                'class' => 'application.modules_core.dashboard.DashboardStreamAction',
                'mode' => BaseStreamAction::MODE_NORMAL,
            ),
        );
    }

    /**
     * Dashboard Index
     *
     * Show recent wall entries for this user
     */
    public function actionIndex()
    {

        if (Yii::app()->user->isGuest) {
            $this->render('index_guest', array());
        } else {
            $this->render('index', array());
        }
    }

    /**
     * Returns a JSON Object which contains a lot of informations about
     * current states like new posts on workspaces
     */
    public function actionGetFrontEndInfo()
    {

        $json = array();
        $json['workspaces'] = array();

        if (Yii::app()->user->isGuest) {
            return CJSON::encode($json);
        }

        $criteria = new CDbCriteria();
        $criteria->order = 'last_visit DESC';

        $memberships = SpaceMembership::model()->with('workspace')->findAllByAttributes(array(
            'user_id' => Yii::app()->user->id,
            'status' => SpaceMembership::STATUS_MEMBER
                ), $criteria);

        foreach ($memberships as $membership) {
            $workspace = $membership->workspace;

            $info = array();
            $info['name'] = CHtml::encode($workspace->name);
            #$info['id'] = $workspace->id;	# should be hidden at frontend
            $info['guid'] = $workspace->guid;
            $info['totalItems'] = $workspace->countItems();
            $info['newItems'] = $membership->countNewItems();

            $json['workspaces'][] = $info;
        }

        $user = Yii::app()->user->getModel();

        $criteria = new CDbCriteria();
        $criteria->condition = 'user_id = :user_id';
        $criteria->addCondition('seen != 1');
        $criteria->params = array('user_id' => $user->id);

        $json['newNotifications'] = Notification::model()->count($criteria);
        $json['notifications'] = array();
        $criteria->addCondition('desktop_notified = 0');
        $notifications = Notification::model()->findAll($criteria);

        foreach ($notifications as $notification) {
            if ($user->getSetting("enable_html5_desktop_notifications", 'core', HSetting::Get('enable_html5_desktop_notifications', 'notification'))) {
                $info = $notification->getOut();
                $info = strip_tags($info);
                $info = str_replace("\n", "", $info);
                $info = str_replace("\r", "", $info);
                $json['notifications'][] = $info;
            }
            $notification->desktop_notified = 1;
            $notification->update();
        }


        print CJSON::encode($json);
        Yii::app()->end();
    }

}
