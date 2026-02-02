<?php

namespace activity\unit;

use Codeception\Specify;
use humhub\modules\activity\controllers\ActivityBoxController;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\activity\tests\codeception\activities\TestContentGroupActivity;
use humhub\modules\activity\widgets\ActivityBox;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Expression;

class GroupingTest extends HumHubDbTestCase
{
    use Specify;

    public function testRemoveGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a4 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a5 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);

        $a1->record->refresh();
        $this->assertEquals(
            5,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a4->record->refresh();
        $a4->record->delete();
        $a2->record->refresh();
        $a2->record->delete();

        $this->assertEquals(
            3,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a3->record->refresh();
        $a3->record->delete();

        // Group Destroyed
        $this->assertEquals(
            1,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );
    }

    public function testAddToGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        // Some other activity
        ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);

        // Check is still ungrouped
        $a1->record->refresh();
        $a3->record->refresh();
        $this->assertNotEquals($a1->record->grouping_key, $a3->record->grouping_key);

        // Check now in group
        $a4 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a1->record->refresh();
        $a3->record->refresh();

        $this->assertEquals($a1->record->grouping_key, $a3->record->grouping_key);
        $this->assertEquals($a4->record->grouping_key, $a1->record->grouping_key);

        $a5 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a1->record->refresh();
        $this->assertEquals($a1->record->grouping_key, $a5->record->grouping_key);

        // Check group of 5
        $this->assertEquals(
            5,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );
    }

    public function testMultiplePostGrouping()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post0 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '0']);
        $post0->save();
        Activity::updateAll(['created_at' => new Expression('created_at - INTERVAL 2 HOUR')], ['content_id' => $post0->content->id]);

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'A']);
        $post1->save();

        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'B']);
        $post2->save();

        $this->becomeUser('User1');
        $this->assertEquals(3, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        $this->becomeUser('User2');
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']);
        $post3->save();
        $post4 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'D']);
        $post4->save();

        $this->becomeUser('User1');
        $query = ActivityBoxController::getQuery($space->contentContainerRecord);
        $this->assertEquals(2, $query->count());

        $activity = ActivityManager::load($query->one());
        $this->assertEquals('Sara Tester created a new post "D" and 3 more.', $activity->asText());
    }

    public function testChangeContentContainer()
    {
        // TODO: Create a group of Activities, Move one into another Content Container, make sure Group is changed
    }

    public function testChangeVisibility()
    {
        // TODO: Create a group of Activities, Move one into another Content Container, make sure Group is changed
    }

}
