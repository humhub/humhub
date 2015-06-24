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
 * Search Controller provides action for searching users.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class SearchController extends Controller
{

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
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * JSON Search for Users
     *
     * Returns an array of users with fields:
     *  - guid
     *  - displayName
     *  - image
     *  - profile link
     */
    public function actionJson()
    {

        $maxResults = 10;
        $results = array();
        $keyword = Yii::app()->request->getParam('keyword');
        $keyword = Yii::app()->input->stripClean($keyword);

        // Build Search Condition
        $criteria = new CDbCriteria();
        $criteria->limit = $maxResults;
        $criteria->condition = 1;
        $criteria->params = array();
        $i = 0;
        foreach (explode(" ", $keyword) as $part) {
            $i++;
            $criteria->condition .= " AND (t.email LIKE :match{$i} OR "
                    . "t.username LIKE :match{$i} OR "
                    . "userProfile.firstname LIKE :match{$i} OR "
                    . "userProfile.lastname LIKE :match{$i} OR "
                    . "userProfile.title LIKE :match{$i})";

            $criteria->params[':match' . $i] = "%" . $part . "%";
        }

        $users = User::model()->with('userProfile')->findAll($criteria);

        foreach ($users as $user) {
            if ($user != null) {
                $userInfo = array();
                $userInfo['guid'] = $user->guid;
                $userInfo['displayName'] = CHtml::encode($user->displayName);
                $userInfo['image'] = $user->getProfileImage()->getUrl();
                $userInfo['link'] = $user->getUrl();
                $results[] = $userInfo;
            }
        }

        print CJSON::encode($results);
        Yii::app()->end();
    }

}

?>
