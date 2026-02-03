<?php

namespace humhub\modules\activity\tests\codeception\unit;

use Codeception\Specify;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ActivityTest extends HumHubDbTestCase
{
    use Specify;

    public function testCreateActivity()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        ActivityManager::dispatch(TestActivity::class, $post);

        $record = Activity::findOne(['class' => TestActivity::class]);
        $this->assertNotNull($record, 'Activity record persisted');

        $testActivity = ActivityManager::load($record);
        $this->assertNotNull($testActivity, 'Get BaseActivity from Activity Record');

        $this->assertEquals(TestActivity::class, $testActivity::class);
        $this->assertEquals($record->content->polymorphicRelation->id, $post->id);
        $this->assertEquals($record->contentcontainer_id, $post->content->contentcontainer_id);
    }

    public function testDeleteRecord()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(1);

        ActivityManager::dispatch(TestActivity::class, $post);

        // Record exists
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));

        // Soft Delete
        $this->assertTrue($post->delete());

        // Record still exists
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));

        // Default Scope filtering soft deleted activities
        $this->assertNull(Activity::find()->defaultScopes(Yii::$app->user->identity)
            ->andWhere(['class' => TestActivity::class])->one());

        $this->assertTrue($post->hardDelete());

        $this->assertNull(Activity::findOne(['class' => TestActivity::class]));


        $post2 = Post::findOne(2);
        ActivityManager::dispatch(TestActivity::class, $post2);
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));
        $post2->content->delete();
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));
        $post2->content->hardDelete();
        $this->assertNull(Activity::findOne(['class' => TestActivity::class]));

    }

    public function testDeleteOriginator()
    {
        $this->becomeUser('User2');

        // Post (User 2 Space 2 Post Public)
        $post = Post::findOne(10);

        ActivityManager::dispatch(TestActivity::class, $post);
        $activityRecord = Activity::findOne(['class' => TestActivity::class]);


        $this->assertNotNull(Activity::findOne(['activity.id' => $activityRecord->id]));
        Yii::$app->user->identity->softDelete();

        // Activity still there
        $this->assertNotNull(Activity::findOne(['activity.id' => $activityRecord->id]));

        Yii::$app->user->identity->delete();

        $this->assertNull(Activity::findOne(['activity.id' => $activityRecord->id]));
    }
}
