<?php

namespace humhub\modules\activity\tests\codeception\unit;

use Codeception\Specify;
use humhub\components\Event;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\events\BeforeDispatchEvent;
use humhub\modules\activity\live\NewActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\services\RenderService;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\comment\activities\NewCommentActivity;
use humhub\modules\comment\models\Comment;
use humhub\modules\live\components\LiveEvent;
use humhub\modules\live\models\Live;
use humhub\modules\post\models\Post;
use humhub\modules\user\activities\FollowActivity;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidCallException;

class ActivityTest extends HumHubDbTestCase
{
    use Specify;

    public function testCreateActivity()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        TestActivity::send($post);

        $record = Activity::findOne(['class' => TestActivity::class]);
        $this->assertNotNull($record, 'Activity record persisted');

        $testActivity = ActivityManager::load($record);
        $this->assertNotNull($testActivity, 'Get BaseActivity from Activity Record');

        $this->assertEquals(TestActivity::class, $testActivity::class);
        $this->assertEquals($record->content->polymorphicRelation->id, $post->id);
        $this->assertEquals($record->contentcontainer_id, $post->content->contentcontainer_id);
    }

    public function testCreateActivityOnGlobalContent()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'Global content']);
        $this->assertTrue($post->save());
        $this->assertNull($post->content->contentcontainer_id);

        // Commenting on global content dispatches NewCommentActivity without a container
        $comment = new Comment([
            'message' => 'Comment on global content',
            'content_id' => $post->content->id,
        ]);
        $this->assertTrue($comment->save());

        $record = Activity::findOne(['class' => NewCommentActivity::class]);
        $this->assertNotNull($record, 'Activity record persisted');
        $this->assertNull($record->contentcontainer_id);

        $activity = ActivityManager::load($record);
        $this->assertNull($activity->contentContainer);

        // The web output is the activity's own sentence now, rendered by the ActivityBox
        // island around it - see ActivitySerializer.
        $this->assertNotEmpty($activity->asWeb());

        $renderService = new RenderService($record);
        $this->assertNotEmpty($renderService->getMailText());
        $this->assertNotEmpty($renderService->getMailHtml());
    }

    public function testDispatchSendsALiveEvent()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        $activity = TestActivity::send($post);
        $event = $this->latestLiveEvent();

        $this->assertInstanceOf(NewActivity::class, $event, 'A new activity announces itself');
        $this->assertEquals($activity->record->id, $event->activityId);
        $this->assertEquals($post->content->contentcontainer_id, $event->contentContainerId);
        $this->assertEquals($post->content->container->guid, $event->containerGuid);
        // The audience of the activity is the audience of the content it is about.
        $this->assertEquals($post->content->visibility, $event->visibility);
    }

    public function testDispatchWithoutContainerSendsNoLiveEvent()
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'Global content']);
        $this->assertTrue($post->save());
        $this->assertNull($post->content->contentcontainer_id);

        Live::deleteAll();
        TestActivity::send($post);

        // The live system routes by container - an activity without one has no audience.
        $this->assertNull($this->latestLiveEvent());
    }

    public function testSendReturnsTheActivityOfTheClass()
    {
        $post = Post::findOne(['id' => 1]);
        $user = User::findOne(['id' => 3]);

        $activity = TestActivity::send($post, $user);

        $this->assertInstanceOf(TestActivity::class, $activity);
        $this->assertSame($user->id, $activity->user->id);
        $this->assertEquals($post->content->id, $activity->record->content_id);
    }

    public function testSendIsVetoedThroughTheEvent()
    {
        $post = Post::findOne(['id' => 1]);
        $user = User::findOne(['id' => 3]);
        $events = [];
        $handler = static function ($event) use (&$events): void {
            $events[] = $event;
            $event->isValid = false;
        };
        Event::on(ActivityManager::class, ActivityManager::EVENT_BEFORE_DISPATCH, $handler);

        try {
            $this->assertNull(TestActivity::send($post, $user));
            $this->assertNull(ActivityManager::dispatch(TestActivity::class, $post, $user), 'the deprecated dispatch() too');
        } finally {
            Event::off(ActivityManager::class, ActivityManager::EVENT_BEFORE_DISPATCH, $handler);
        }

        $this->assertNull(Activity::findOne(['class' => TestActivity::class]));
        $this->assertCount(2, $events);
        $this->assertInstanceOf(BeforeDispatchEvent::class, $events[0]);
        $this->assertSame(TestActivity::class, $events[0]->class);
        $this->assertSame($post, $events[0]->target);
        $this->assertSame($user, $events[0]->user);
    }

    public function testDeprecatedDispatchStillWorks()
    {
        $this->becomeUser('User2');

        $activity = ActivityManager::dispatch(TestActivity::class, Post::findOne(['id' => 1]));

        $this->assertInstanceOf(TestActivity::class, $activity);
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));
    }

    public function testSendNeedsAConcreteClass()
    {
        $this->expectException(InvalidCallException::class);
        BaseContentActivity::send(Post::findOne(['id' => 1]), User::findOne(['id' => 3]));
    }

    public function testUrlIsRelativeByDefault()
    {
        $followed = User::findOne(['id' => 1]);
        $activity = FollowActivity::send($followed, User::findOne(['id' => 3]));

        $this->assertSame($followed->getUrl(false), $activity->getUrl());
        $this->assertSame($followed->getUrl(true), $activity->getUrl(true));
        // the summary mail links absolute
        $this->assertStringContainsString(
            urldecode((string)$followed->getUrl(true)),
            (new RenderService($activity->record))->getMailText(),
        );
    }

    private function latestLiveEvent(): ?LiveEvent
    {
        $record = Live::find()->orderBy(['id' => SORT_DESC])->one();

        return $record === null ? null : LiveEvent::fromSerialized($record->serialized_data);
    }

    public function testDeleteRecord()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(1);

        TestActivity::send($post);

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

        /*
        $post2 = Post::findOne(2);
        TestActivity::send($post2);
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));
        $post2->content->delete();
        $this->assertNotNull(Activity::findOne(['class' => TestActivity::class]));
        $post2->content->hardDelete();
        $this->assertNull(Activity::findOne(['class' => TestActivity::class]));
        */
    }

    public function testDeleteOriginator()
    {
        $this->becomeUser('User2');

        // Post (User 2 Space 2 Post Public)
        $post = Post::findOne(10);

        TestActivity::send($post);
        $activityRecord = Activity::findOne(['class' => TestActivity::class]);


        $this->assertNotNull(Activity::findOne(['activity.id' => $activityRecord->id]));
        Yii::$app->user->identity->softDelete();

        // Activity still there
        $this->assertNotNull(Activity::findOne(['activity.id' => $activityRecord->id]));

        Yii::$app->user->identity->delete();

        $this->assertNull(Activity::findOne(['activity.id' => $activityRecord->id]));
    }
}
