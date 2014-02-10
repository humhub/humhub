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

}

?>
