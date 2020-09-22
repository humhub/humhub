<?php

namespace humhub\modules\activity\tests\codeception\unit;

use humhub\modules\activity\models\Activity;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

class DeleteActivityTest extends HumHubDbTestCase
{
    public function testDeleteRecord()
    {
        $post = Post::findOne(1);
        $activity = TestActivity::instance()->about($post)->create();
        $record = $activity->record;
        $this->assertNotNull(Activity::findOne(['id' => $record->id]));
        $post->delete();
        $this->assertNull(Activity::findOne(['id' => $record->id]));
    }

    public function testDeleteOriginator()
    {
        $post = Post::findOne(1);
        $activity = TestActivity::instance()->about($post)->create();
        $record = $activity->record;
        $this->assertNotNull(Activity::findOne(['id' => $record->id]));
        $post->createdBy->delete();
        $this->assertNull(Activity::findOne(['id' => $record->id]));
    }
}