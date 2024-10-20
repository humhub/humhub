<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\commands;

use humhub\modules\content\jobs\SearchRebuildIndex;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\queue\helpers\QueueHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\console\Controller;

class SearchController extends Controller
{
    /**
     * Optimizes the search index
     */
    public function actionOptimize()
    {
        print "Optimizing search index: ";
        $driver = ContentSearchService::getDriver();
        $driver->optimize();
        print "OK!\n\n";
    }

    /**
     * Rebuilds the search index
     */
    public function actionRebuild()
    {
        $driver = ContentSearchService::getDriver();
        $driver->purge();
        foreach (Content::find()->each() as $content) {
            (new ContentSearchService($content))->update(false);
            print ".";
        }
        print "OK!\n\n";
    }

    /**
     * Queue search index rebuild
     */
    public function actionQueueRebuild()
    {
        $job = new SearchRebuildIndex();
        if (QueueHelper::isQueued($job)) {
            print "Rebuild process is already queued or running!\n";
            return;
        }

        Yii::$app->queue->push($job);
    }

    /**
     * Search the index
     *
     * @param string $searchString
     */
    public function actionFind($keyword)
    {
        $driver = ContentSearchService::getDriver();

        $user = User::findOne(['id' => 1]);

        $options = new SearchRequest(['keyword' => $keyword, 'user' => $user]);

        $searchResultSet = $driver->search($options);

        print "Number of hits: " . $searchResultSet->pagination->totalCount . "\n";

        print "Results:\n";
        foreach ($searchResultSet->results as $content) {
            print "\t - " . $content->object_model . ' ' . $content->object_id . "\n";
        }
    }
}
