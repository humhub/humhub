<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\commands;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\ZendLucenceDriver;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\search\commands\type;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\jobs\RebuildIndex;
use humhub\modules\user\models\User;
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
        $driver = $this->getDriver();
        $driver->purge();
        foreach (Content::find()->all() as $content) {
            if ($content->getPolymorphicRelation() instanceof Searchable) {
                $driver->update($content);
                print ".";
            }
        }
        print "OK!\n\n";
    }

    /**
     * Queue search index rebuild
     */
    public function actionQueueRebuild()
    {
        $job = new RebuildIndex();
        if (\humhub\modules\queue\helpers\QueueHelper::isQueued($job)) {
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
        $driver = $this->getDriver();

        $user = User::findOne(['id' => 1]);

        $options = new SearchRequest(['keyword' => $keyword, 'user' => $user]);

        $searchResultSet = $driver->search($options);

        print "Number of hits: " . $searchResultSet->pagination->totalCount . "\n";

        print "Results:\n";
        foreach ($searchResultSet->results as $content) {
            print "\t - " . $content->object_model . ' ' . $content->object_id . "\n";
        }
    }


    private function getDriver(): AbstractDriver
    {
        return new ZendLucenceDriver();
    }
}
