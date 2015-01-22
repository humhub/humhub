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
class DashboardController extends Controller {

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Dashboard Index
     *
     * Show recent wall entries for this user
     */
    public function actionIndex() {

        // contains the current version to show the welcome modal
        $version = 1;

        $this->render('index', array('version' => $version));
    }

    /**
     * Page which shows all current workspaces
     */
    public function actionSpaces() {

        $pageSize = 5;

        $criteria = new CDbCriteria();
        $criteria->condition = 'visibility != ' . Space::VISIBILITY_NONE;
        $criteria->order = 'id DESC';
        //$criteria->params = array (':id'=>$id);

        $workspaceCount = Space::model()->count($criteria);

        $pages = new CPagination($workspaceCount);
        $pages->setPageSize($pageSize);
        $pages->applyLimit($criteria);  // the trick is here!

        $workspaces = Space::model()->findAll($criteria);

        $this->render('workspaces', array(
            'workspaces' => $workspaces,
            'pages' => $pages,
            'workspaceCount' => $workspaceCount,
            'pageSize' => $pageSize,
        ));
    }

    /**
     * Page which shows all people
     */
    public function actionPeople() {

        $criteria = new CDbCriteria();
        //$criteria->condition = 'visibility != '.Space::VISIBILITY_NONE;
        $criteria->order = 'firstname ASC';

        $userCount = User::model()->count($criteria);

        $pages = new CPagination($userCount);
        $pages->setPageSize(HSetting::Get('paginationSize'));
        $pages->applyLimit($criteria);  // the trick is here!

        $users = User::model()->findAll($criteria);

        $this->render('people', array(
            'users' => $users,
            'pages' => $pages,
            'userCount' => $userCount,
            'pageSize' => HSetting::Get('paginationSize'),
        ));
    }

    /**
     * Returns a JSON Object which contains a lot of informations about
     * current states like new posts on workspaces
     */
    public function actionGetFrontEndInfo() {

        $json = array();
        $json['workspaces'] = array();

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

        // New notification count
        $sql = "SELECT count(id)
		FROM notification
		WHERE  user_id = :user_id AND seen != 1";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $userId = Yii::app()->user->id;
        $command->bindParam(":user_id", $userId);
        $json['newNotifications'] = $command->queryScalar();

        print CJSON::encode($json);
        Yii::app()->end();
    }

}
