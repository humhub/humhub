<?php

namespace tests\codeception\unit\modules\activity;

use Yii;
use yii\codeception\DbTestCase;
use humhub\modules\activity\components\BaseActivity;
use tests\codeception\unit\modules\activity\data\TestActivity;

class BaseActivityTest extends DbTestCase
{

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testInvalidSource()
    {
        $activity = new TestActivity();
        $activity->source = $this;
        $activity->create();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testNoSource()
    {
        $activity = new TestActivity();
        $activity->create();
    }

}
