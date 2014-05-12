<?php

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
     * Returns an json array of results.
     *
     * Fields:
     *  - guid
     *  - firstname
     *  - lastname
     *  - city
     *  - image
     *  - profile link
     *  - isMember
     *
     * @todo Add limit for workspaces
     */
    public function actionJson()
    {

        $results = array();
        $keyword = Yii::app()->request->getParam('keyword', ""); // guid of user/workspace
        $spaceId = (int)Yii::app()->request->getParam('space_id', 0);

        // save current displayNameFormat for users
        $displayFormat = HSetting::Get('displayNameFormat');

        // get members of the current space
        $spaceMembers = SpaceMembership::model()->findAll('space_id=:space_id', array(':space_id' => $spaceId));


        if ($displayFormat == "{username}") {

            // build like search string
            $match = addcslashes($keyword, '%_');

            // build sql string
            $q = new CDbCriteria();
            $q->addSearchCondition('username', $match);

            // find users by committed keyword
            $users = User::model()->findAll( $q );

            foreach ($users as $user) {

                if ($user != null) {

                    // push array with new user entry
                    $userInfo = array();
                    $userInfo['guid'] = $user->guid;
                    $userInfo['displayName'] = $user->displayName;
                    $userInfo['image'] = $user->getProfileImage()->getUrl();
                    $userInfo['link'] = $user->getUrl();
                    $userInfo['isMember'] = $this->checkMembership($spaceMembers, $user->id);
                    $results[] = $userInfo;

                }
            }

        } else {

            // get matching database rows
            $profiles = Yii::app()->db->createCommand("SELECT user_id FROM profile WHERE firstname like '%" . $keyword . "%' OR lastname like '%" . $keyword . "%'")->queryAll();

            // save rows count
            $hitCount = count($profiles);

            // close function, if there are no results
            if ($hitCount == 0) {
                print CJSON::encode($results);
                Yii::app()->end();
            }

            foreach ($profiles as $profile) {

                // get user id
                $userId = $profile['user_id'];

                // find user in database
                $user = User::model()->findByPk($userId);

                if ($user != null) {

                    // push array with new user entry
                    $userInfo = array();
                    $userInfo['guid'] = $user->guid;
                    $userInfo['displayName'] = $user->displayName;
                    $userInfo['image'] = $user->getProfileImage()->getUrl();
                    $userInfo['link'] = $user->getUrl();
                    $userInfo['isMember'] = $this->checkMembership($spaceMembers, $userId);
                    $results[] = $userInfo;

                }
            }

        }

        print CJSON::encode($results);
        Yii::app()->end();

    }

    /**
     * check Membership of users
     *
     */
    private function checkMembership($members, $userId)
    {

        // check if current user is member of this space
        foreach ($members as $member) {
            if ($userId == $member->user_id) {
                return true;
                break;
            }
        }

        return false;
    }

}

?>
