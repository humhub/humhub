<?php

namespace humhub\modules\activity\tests\codeception\unit;

use Codeception\Specify;
use humhub\modules\activity\helpers\ActivityHelper;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\ActiveQuery;

class ActivityTest extends HumHubDbTestCase
{
    use Specify;

    public function testCreateActivity()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        $activity = TestActivity::instance()->from(Yii::$app->user->getIdentity())->about($post);

        // Test Originator
        $this->assertEquals($activity->originator->id, Yii::$app->user->getIdentity()->id, 'Originator id before save');
        $this->assertEquals($activity->record->content->created_by, Yii::$app->user->getIdentity()->id, 'Content originator before save');
        $this->assertEquals($activity->record->content->contentcontainer_id, $post->content->container->id, 'ContentContainer before save');

        // Test Source
        $this->assertEquals($activity->source->id, $post->id, 'Source id before save');
        $this->assertEquals(get_class($activity->source), Post::class, 'Source class before save');

        // Test Activity Record
        $this->assertNotNull($activity->record, 'BaseActivity Record not null');

        $activity->create();

        $record = Activity::findOne(['class' => TestActivity::class]);
        $this->assertEquals($record->module, 'test');
        $source = $record->getPolymorphicRelation();

        $this->assertNotNull($record, 'Activity record persisted');

        $testActivity = $record->getActivityBaseClass();
        $this->assertNotNull($testActivity, 'Get BaseActivity from Activity Record');

        $this->assertEquals(get_class($activity), get_class($testActivity));
        $this->assertEquals(get_class($source), get_class($testActivity->source), 'Activity source after reload');
        $this->assertEquals($source->id, $testActivity->source->id, 'Activity Source id after reload');

        $this->assertNotNull($testActivity->getContent(), 'Activity::getContent');
        $this->assertEquals($testActivity->getContent()->id, $post->content->id, 'Compare activity content with source content.');

        $this->assertEquals($testActivity->getContentContainer()->id, $post->content->container->id, 'Activity::getContentContainer content');
    }

    public function testCreateActivityAboutOnly()
    {
        $post = Post::findOne(['id' => 1]);
        $activity = TestActivity::instance()->about($post)->create();
        $this->assertEquals($post->content->created_by, $activity->record->content->created_by);

        $activity = Activity::findOne(['id' => $activity->record->id]);

        $this->assertEquals($post->content->created_by, $activity->getActivityBaseClass()->originator->id);
    }

    public function testActivityVisibilitySynchronization()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        $post1 = $this->createPostWithVisibility($space, Content::VISIBILITY_PRIVATE);
        $this->checkActivityVisibility($post1, Content::VISIBILITY_PRIVATE);
        $post1->content->visibility = Content::VISIBILITY_PUBLIC;
        $this->assertTrue($post1->content->save());
        $this->checkActivityVisibility($post1, Content::VISIBILITY_PUBLIC);

        $post2 = $this->createPostWithVisibility($space, Content::VISIBILITY_PUBLIC);
        $this->checkActivityVisibility($post2, Content::VISIBILITY_PUBLIC);
        $post2->content->visibility = Content::VISIBILITY_PRIVATE;
        $this->assertTrue($post2->content->save());
        $this->checkActivityVisibility($post2, Content::VISIBILITY_PRIVATE);
    }

    private function createPostWithVisibility($space, $visibility): Post
    {
        $post = new Post($space, ['message' => 'The post was created with visibility = ' . $visibility]);
        $post->content->visibility = $visibility;
        $this->assertTrue($post->save());
        $post->refresh();
        $this->assertEquals($visibility, $post->content->visibility);

        return $post;
    }

    private function checkActivityVisibility($model, $visibility)
    {
        $activitiesQuery = ActivityHelper::getActivitiesQuery($model);
        if ($activitiesQuery instanceof ActiveQuery) {
            foreach ($activitiesQuery->each() as $activity) {
                /* @var Activity $activity */
                $this->assertEquals($visibility, $activity->content->visibility);
            }
        }
    }
}
