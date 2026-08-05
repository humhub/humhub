<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\notification\models\Notification;
use Codeception\Specify;

class LoadNotificationTest extends HumHubDbTestCase
{
    use Specify;

    public function testDefaultView()
    {
        $this->becomeUser('User1');

        // loadMore() uses keyset pagination: each next page is requested
        // using the cursor of the last loaded group, not an offset or a
        // plain notification id, since results are ordered by aggregate
        // columns that have no fixed relation to either of those.
        $notifications = Notification::loadMore();
        $this->assertEquals(6, count($notifications));

        $ids = array_map(static fn($o) => $o->id, $notifications);
        $this->assertEquals(18, max($ids));
        $this->assertEquals(13, min($ids));

        $cursor = end($notifications)->getPagingCursor();
        $notifications = Notification::loadMore($cursor);
        $this->assertEquals(6, count($notifications));

        $ids = array_map(static fn($o) => $o->id, $notifications);
        $this->assertEquals(12, max($ids));
        $this->assertEquals(7, min($ids));

        $cursor = end($notifications)->getPagingCursor();
        $notifications = Notification::loadMore($cursor);
        $this->assertEquals(6, count($notifications));

        $ids = array_map(static fn($o) => $o->id, $notifications);
        $this->assertEquals(6, max($ids));
        $this->assertEquals(1, min($ids));
    }
}
