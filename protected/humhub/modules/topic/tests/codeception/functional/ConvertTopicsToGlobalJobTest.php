<?php

namespace humhub\modules\topic\tests\codeception\functional;

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
        $topic1Name = 'Test Topic ' . Yii::$app->security->generateRandomString(5);
        $topic2Name = 'Test Topic ' . Yii::$app->security->generateRandomString(5);

        $topic1 = new Topic([
            'name' => $topic1Name,
            'module_id' => (new Topic())->moduleId,
            'type' => Topic::class,
            'contentcontainer_id' => 1,
        ]);
        $topic2 = new Topic([
            'name' => $topic2Name,
            'module_id' => (new Topic())->moduleId,
            'type' => Topic::class,
            'contentcontainer_id' => 2,
        ]);
        $topic3 = new Topic([
            'name' => $topic2Name,
            'module_id' => (new Topic())->moduleId,
            'type' => Topic::class,
            'contentcontainer_id' => 3,
        ]);

        $topic1->save();
        $topic2->save();
        $topic3->save();

        $relation1 = new ContentTagRelation([
            'content_id' => 1,
            'tag_id' => $topic1->id,
        ]);

        $relation2 = new ContentTagRelation([
            'content_id' => 2,
            'tag_id' => $topic2->id,
        ]);

        $relation3 = new ContentTagRelation([
            'content_id' => 3,
            'tag_id' => $topic3->id,
        ]);

        $relation1->save();
        $relation2->save();
        $relation3->save();

        Yii::$app->queue->push(new ConvertTopicsToGlobalJob());
        Yii::$app->queue->run();

        // Check if relations to non-global topics are deleted
        $this->assertEmpty(ContentTagRelation::findOne(['tag_id' => $topic1->id]));
        $this->assertEmpty(ContentTagRelation::findOne(['tag_id' => $topic2->id]));
        $this->assertEmpty(ContentTagRelation::findOne(['tag_id' => $topic3->id]));

        // Check if non-global topics are deleted
        $this->assertFalse($topic1->refresh());
        $this->assertFalse($topic2->refresh());
        $this->assertFalse($topic3->refresh());

        $globalTopic1 = Topic::findOne(['name' => $topic1Name, 'contentcontainer_id' => null]);
        $globalTopic2 = Topic::findOne(['name' => $topic2Name, 'contentcontainer_id' => null]);

        // Check if 2 global topics are created
        $this->assertNotEmpty($globalTopic1);
        $this->assertNotEmpty($globalTopic2);

        $globalTopic1Relations = ContentTagRelation::find()->select('content_id')->where(['tag_id' => $globalTopic1->id])->column();
        $globalTopic2Relations = ContentTagRelation::find()->select('content_id')->where(['tag_id' => $globalTopic2->id])->column();

        // Check if relations are created to global topics and the correct content_id are assigned
        $this->assertEquals(1, count($globalTopic1Relations));
        $this->assertEquals(2, count($globalTopic2Relations));
        $this->assertContains(1, $globalTopic1Relations);
        $this->assertContains(2, $globalTopic2Relations);
        $this->assertContains(3, $globalTopic2Relations);
    }
}
