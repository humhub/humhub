<?php

namespace humhub\modules\activity\tests\codeception\unit;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;

class ActivityTestTest extends HumHubDbTestCase
{

    use Specify;

    public function testCreateActivity()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);
        
        $activity = activities\TestActivity::instance()
                ->from(Yii::$app->user->getIdentity())
                ->about($post);
        
        // Test Originator
        $this->assertEquals($activity->originator->id, Yii::$app->user->getIdentity()->id, 'Originator id before save');
        $this->assertEquals($activity->record->content->created_by, Yii::$app->user->getIdentity()->id, 'Content originator before save');
        $this->assertEquals($activity->record->content->contentcontainer_id, $post->content->container->id, 'ContentContainer before save');
        
        // Test Source
        $this->assertEquals($activity->source->id, $post->id, 'Source id before save');
        $this->assertEquals($activity->source->className(), $post->className(), 'Source class before save');
        
        // Test Activity Record
        $this->assertNotNull($activity->record, 'BaseActivity Record not null');
        
        $activity->save();
        
        $record = \humhub\modules\activity\models\Activity::findOne(['class' => activities\TestActivity::class]);
        $this->assertEquals($record->module, 'test');
        $source = $record->getPolymorphicRelation();
       
        $this->assertNotNull($record, 'Activity record persisted');
        
        $testActivity = $record->getActivityBaseClass();
        $this->assertNotNull($testActivity, 'Get BaseActivity from Activity Record');
        
        $this->assertEquals($activity->className(), $testActivity->className());
        $this->assertEquals($source->className(), $testActivity->source->className(), 'Activity source after reload');
        $this->assertEquals($source->id, $testActivity->source->id, 'Activity Source id after reload');
        
        $this->assertNotNull($testActivity->getContent(), 'Activity::getContent');
        $this->assertEquals($testActivity->getContent()->id, $post->content->id, 'Compare activity content with source content.');
        
        $this->assertEquals($testActivity->getContentContainer()->id, $post->content->container->id, 'Activity::getContentContainer content');
    }
}
