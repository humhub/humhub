<?php

namespace tests\codeception\unit\modules\notification;

use yii\codeception\DbTestCase;
use tests\codeception\unit\modules\notification\data\TestNotification;

class BaseNotificationTest extends DbTestCase
{

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testInvalidSource()
    {
        $activity = new TestNotification();
        $activity->source = $this;
        $activity->create();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testNoSource()
    {
        $activity = new TestNotification();
        $activity->create();
    }

}
