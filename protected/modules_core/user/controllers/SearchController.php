<?php

/**
 * Search Controller provides action for searching users.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class SearchController extends Controller {

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
     *
     * @todo Add limit for workspaces
     */
    public function actionJson() {

        $results = array();
        $keyword = Yii::app()->request->getParam('keyword', ""); // guid of user/workspace
        $page = (int) Yii::app()->request->getParam('page', 1); // current page (pagination)
        $limit = (int) Yii::app()->request->getParam('limit', HSetting::Get('paginationSize')); // current page (pagination)
        $spaceId = (int) Yii::app()->request->getParam('space_id', 0);
        $hitCount = 0;

        $keyword = Yii::app()->input->stripClean($keyword);

        // We need a least 3 characters
        if (strlen($keyword) < 3) {
            print CJSON::encode($results);
            Yii::app()->end();
        }

        if (strlen($keyword) > 2) {

            if (strpos($keyword, "@") === false) {
                $keyword = str_replace(".", "", $keyword);
                $query = "(title:" . $keyword . "* OR email:" . $keyword . "*) AND (model:User)";
            } else {
                $query = "email:" . $keyword . " AND (model:User)";
            }

            // get members of the current space
            $spaceMembers = SpaceMembership::model()->findAll('space_id=:space_id', array(':space_id'=>$spaceId));

            //$hits = HSearch::getInstance()->Find($query);
            //, $limit, $page
            $hits = new ArrayObject(
                    HSearch::getInstance()->Find($query
            ));

            $hitCount = count($hits);

            // Limit Hits
            $hits = new LimitIterator($hits->getIterator(), ($page - 1) * $limit, $limit);

            if ($hitCount == 0) {
                print CJSON::encode($results);
                Yii::app()->end();
            }


            foreach ($hits as $hit) {
                $doc = $hit->getDocument();
                $model = $doc->getField("model")->value;

                if ($model == "User") {
                    $userId = $doc->getField('pk')->value;
                    $user = User::model()->findByPk($userId);

                    if ($user != null) {
                        $userInfo = array();
                        $userInfo['guid'] = $user->guid;
                        $userInfo['displayName'] = $user->displayName;
                        $userInfo['image'] = $user->getProfileImage()->getUrl();
                        $userInfo['link'] = $user->getUrl();
                        $userInfo['isMember'] = $this->checkMembership($spaceMembers, $userId);
                        $results[] = $userInfo;
                    } else {
                        Yii::log("Could not load use with id " . $userId . " from search index!", CLogger::LEVEL_ERROR);
                    }
                } else {
                    Yii::log("Got no user hit from search index!", CLogger::LEVEL_ERROR);
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
    private function checkMembership($members, $userId) {

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
