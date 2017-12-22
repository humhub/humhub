<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\commands;

use Yii;

/**
 * Search Tools
 *
 * @package humhub.modules_core.search.console
 * @since 0.12
 */
class SearchController extends \yii\console\Controller
{

    /**
     * Optimizes the search index
     */
    public function actionOptimize()
    {
        print "Optimizing search index: ";
        Yii::$app->search->optimize();
        print "OK!\n\n";
    }

    /**
     * Rebuilds the search index
     */
    public function actionRebuild()
    {
        print "Rebuild search index: ";
        Yii::$app->search->rebuild();
        print "OK!\n\n";
    }

    /**
     * Search the index
     *
     * @param string $searchString
     * @return type
     */
    public function actionFind($keyword)
    {
        $pageSize = 10;
        $model = "";
        $page = 1;

        print "Searching for: " . $keyword . " \n";

        $results = Yii::$app->search->find($keyword, [
            'pageSize' => $pageSize,
            'page' => $page,
            'model' => ($model != "") ? explode(",", $model) : null
        ]);

        print_r($results);
    }

}
