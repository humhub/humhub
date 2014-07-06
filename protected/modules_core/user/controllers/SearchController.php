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
                    . "profile.firstname LIKE :match{$i} OR "
                    . "profile.lastname LIKE :match{$i} OR "
                    . "profile.title LIKE :match{$i})";

            $criteria->params[':match' . $i] = "%" . $part . "%";
        }

        $users = User::model()->with('profile')->findAll($criteria);

        foreach ($users as $user) {
            if ($user != null) {
                $userInfo = array();
                $userInfo['guid'] = $user->guid;
                $userInfo['displayName'] = $user->displayName;
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
