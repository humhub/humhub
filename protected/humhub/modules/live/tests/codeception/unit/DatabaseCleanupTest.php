<?php

namespace tests\codeception\unit\modules\live;

use humhub\modules\live\driver\Poll;
use humhub\modules\live\jobs\DatabaseCleanup;
use humhub\modules\live\models\Live;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class DatabaseCleanupTest extends HumHubDbTestCase
{
    public function testRemovesOnlyExpiredEvents()
    {
        $driver = new Poll(['maxLiveEventAge' => 600]);
        Yii::$app->live->driver = $driver;

        Live::deleteAll();
        $now = time();
        $expired = $this->createLiveEvent($now - 700);
        $recent = $this->createLiveEvent($now - 500);

        (new DatabaseCleanup())->run();

        static::assertNull(Live::findOne($expired));
        static::assertNotNull(Live::findOne($recent));
    }

    private function createLiveEvent(int $createdAt): int
    {
        $live = new Live();
        $live->serialized_data = 'x';
        $live->created_at = $createdAt;
        static::assertTrue($live->save());

        return $live->id;
    }
}
