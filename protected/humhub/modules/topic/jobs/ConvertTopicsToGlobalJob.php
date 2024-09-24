<?php

namespace humhub\modules\topic\jobs;

use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\topic\models\Topic;
use Yii;

class ConvertTopicsToGlobalJob extends ActiveJob implements ExclusiveJobInterface
{
    public function getExclusiveJobId()
    {
        return 'module.topics.global-conversion';
    }

    public function run()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $topics = Topic::find()
                ->where(['IS NOT', 'contentcontainer_id', null])
                ->andWhere(['module_id' => (new Topic())->moduleId])
                ->all();
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
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
