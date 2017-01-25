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

        $notifications = Notification::loadMore();
        $this->assertEquals(6, count($notifications));
        
        $ids = array_map(create_function('$o', 'return $o->id;'), $notifications);
        $this->assertEquals(18, max($ids));
        $this->assertEquals(13, min($ids));
        
        $notifications = Notification::loadMore(13);
        $this->assertEquals(6, count($notifications));
        
        $ids = array_map(create_function('$o', 'return $o->id;'), $notifications);
        $this->assertEquals(12, max($ids));
        $this->assertEquals(7, min($ids));
        
        $notifications = Notification::loadMore(7);
        $this->assertEquals(6, count($notifications));
        
        $ids = array_map(create_function('$o', 'return $o->id;'), $notifications);
        $this->assertEquals(6, max($ids));
        $this->assertEquals(1, min($ids));
    }

}
