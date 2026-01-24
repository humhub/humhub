<?php

namespace activity\unit;

use Codeception\Specify;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\activity\tests\codeception\activities\TestGroupActivity;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class GroupingTest extends HumHubDbTestCase
{
    use Specify;

    public function testAddToGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        // Some other activity
        ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestGroupActivity::class, $post);

        // Check is still ungrouped
        $a1->record->refresh();
        $a3->record->refresh();
        $this->assertNotEquals($a1->record->grouping_key, $a3->record->grouping_key);

        // Check now in group
        $a4 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a1->record->refresh();
        $a3->record->refresh();
        $this->assertEquals($a1->record->grouping_key, $a3->record->grouping_key);
        $this->assertEquals($a4->record->grouping_key, $a1->record->grouping_key);

        $a5 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a1->record->refresh();
        $this->assertEquals($a1->record->grouping_key, $a5->record->grouping_key);

        // Check group of 5
        $this->assertEquals(
            5,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a4->record->refresh();
        $a4->record->delete();

        // Check group of 4
        $this->assertEquals(
            4,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a3->record->refresh();
        $a3->record->delete();

        // Check group of 3
        $this->assertEquals(
            3,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a2->record->refresh();
        $a2->record->delete();

        // Check no group (count 1)
        $a1->record->refresh();
        $this->assertEquals(
            1,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );
    }

    public function testList()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        $b1 = ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $b2 = ActivityManager::dispatch(TestActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a4 = ActivityManager::dispatch(TestGroupActivity::class, $post);

        $b3 = ActivityManager::dispatch(TestActivity::class, $post);

        $this->assertEquals(7, Activity::find()->count());
        $this->assertEquals(4, Activity::find()->enableGrouping()->count());
    }


    public function testGrouping() {
        $this->becomeUser('User2');

        $post = Post::findOne(['id' => 1]);
        $a1 = ActivityManager::dispatch(TestGroupActivity::class, $post);

        $activity = ActivityManager::load(Activity::find()->enableGrouping()->one());
        $this->assertStringContainsString('Single', $activity->asText());

        $a2 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestGroupActivity::class, $post);
        $a4 = ActivityManager::dispatch(TestGroupActivity::class, $post);

        // Try loading with grouping count
        $activity = ActivityManager::load(Activity::find()->enableGrouping()->one());
        $this->assertStringContainsString('Grouped', $activity->asText());

        // Try lazy load group count
        $activity2 = ActivityManager::load(Activity::findOne(['activity.id' => $activity->record->id]));
        $this->assertStringContainsString('Grouped', $activity2->asText());
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
