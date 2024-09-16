<?php

namespace humhub\modules\topic\jobs;

use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\topic\models\Topic;

class ConvertTopicsToGlobalJob extends ActiveJob implements ExclusiveJobInterface
{
    public function getExclusiveJobId()
    {
        return 'module.topics.global-conversion';
    }

    public function run()
    {
        //        sleep(20);
        //        return;
        $topics = Topic::find()->where(['IS NOT', 'contentcontainer_id', null])->all();

        foreach ($topics as $topic) {
            $existingGlobalTopic = Topic::find()->where(['name' => $topic->name, 'contentcontainer_id' => null])->one();

            if ($existingGlobalTopic) {
                $globalTopic = $existingGlobalTopic;
            } else {
                $globalTopic = new Topic([
                    'name' => $topic->name,
                    'module_id' => $topic->module_id,
                    'type' => $topic->type,
                    'color' => $topic->color,
                    'sort_order' => $topic->sort_order,
                    'contentcontainer_id' => null,
                ]);

                $globalTopic->save();
            }

            ContentTagRelation::updateAll(['tag_id' => $globalTopic->id], ['tag_id' => $topic->id]);

            $topic->delete();
        }
    }
}
