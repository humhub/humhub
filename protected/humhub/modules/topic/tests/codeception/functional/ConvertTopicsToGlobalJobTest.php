<?php

namespace tests\functional;

use Codeception\Test\Unit;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\topic\jobs\ConvertTopicsToGlobalJob;
use humhub\modules\topic\models\Topic;
use Yii;
use yii\queue\sync\Queue;

class ConvertTopicsToGlobalJobTest extends Unit
{
    public function _before()
    {
        Yii::$app->set('queue', [
            'class' => Queue::class,
        ]);
    }

    public function testConvertTopicsToGlobal()
    {
        $topic = new Topic([
            'name' => 'Test Topic',
            'module_id' => 1,
            'type' => 'default',
            'color' => '#000000',
            'sort_order' => 1,
            'contentcontainer_id' => 1,
        ]);
        $topic->save();

        Yii::$app->queue->push(new ConvertTopicsToGlobalJob());

        Yii::$app->queue->run();

        $globalTopic = Topic::findOne(['name' => 'Test Topic', 'contentcontainer_id' => null]);
        $this->assertNotNull($globalTopic);

        $this->assertNotEmpty(ContentTagRelation::findAll(['tag_id' => $globalTopic->id]));
    }
}
