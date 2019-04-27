<?php

namespace humhub\modules\activity\tests\codeception\unit;

use humhub\modules\activity\tests\codeception\activities\TestActivityDefaultLayout;
use humhub\modules\activity\tests\codeception\activities\TestViewActivity;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ActivityViewTest extends HumHubDbTestCase
{
    public function testRenderStreamEntryWithActivityView()
    {
        $activity = TestViewActivity::instance()->from(User::findOne(['id' => 1]))
            ->about(Post::findOne(['id' => 1]))->create();

        $this->assertNotNull($activity->record);
        $wallout = $activity->record->getWallOut();
        $this->assertContains('My special activity view layout', $wallout);
        $this->assertContains('My special activity view content', $wallout);
    }

    public function testRenderStreamEntryWithActivityWithoutView()
    {
        $activity = TestActivity::instance()->from(User::findOne(['id' => 1]))
            ->about(Post::findOne(['id' => 1]))->create();

        $this->assertNotNull($activity->record);
        $wallout = $activity->record->getWallOut();
        $this->assertContains('My special activity view layout without view', $wallout);
        $this->assertContains('Content of no view activity', $wallout);
    }

    public function testRenderWithoutLayoutAndView()
    {
        $activity = TestActivityDefaultLayout::instance()->from(User::findOne(['id' => 1]))
            ->about(Post::findOne(['id' => 1]))->create();

        $this->assertNotNull($activity->record);
        $wallout = $activity->record->getWallOut();
        $this->assertContains('Content of default layout activity', $wallout);
        $this->assertContains('media-object img-rounded', $wallout);
    }
}