<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\topic\models\Topic;
use humhub\components\Widget;

class TrendingTopics extends Widget
{
    public function run()
    {
        // Query most used topics
        $trendingTopics = Topic::find()
            ->leftJoin('content_tag_relation', 'content_tag_relation.tag_id = content_tag.id')
            ->select(['content_tag.*', 'COUNT(content_tag_relation.tag_id) as tag_count'])
            ->groupBy('content_tag.id')
            ->orderBy(['tag_count' => SORT_DESC])
            ->limit(100)
            ->all();

        return $this->render('trendingTopics', ['topics' => $trendingTopics]);
    }
}
