<?php
namespace humhub\tests\codeception\unit\models;

use DateTime;
use humhub\widgets\TimeAgo;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class TimeAgoWidgetTest extends HumHubDbTestCase
{
    public function _before()
    {
        Yii::$app->params['formatter']['timeAgoStatic'] = false;
        Yii::$app->params['formatter']['timeAgoBefore'] = 172800;
        Yii::$app->params['formatter']['timeAgoHideTimeAfter'] = 259200;
    }

    //DateTime::createFromFormat("Y-m-d H:i:s", $timestamp)

    public function testDefaultSettingsTimeAgoBeforeActiveInIntveral()
    {
        // TS within default timAgoBefore interval (172800 s)
        $ts = time() - 60;
        $result = TimeAgo::widget(['timestamp' => $ts]);
        $this->assertTimeAgoActive($result);
    }

    public function testOverwriteDefaultTimeAgoBefore()
    {
        // TS outside of overwritten ts (50 s)
        $ts = time() - 60;
        $result = TimeAgo::widget(['timestamp' => $ts, 'timeAgoBefore' => 50]);
        $this->assertTimeAgoNotActive($result);
    }

    public function testDeactivateTimeAgoBeforeInWidget()
    {
        // TS outside of default but overwritten
        $ts = time() - (172800 + 10);
        $result = TimeAgo::widget(['timestamp' => $ts, 'timeAgoBefore' => false]);
        $this->assertTimeAgoActive($result);
    }

    public function testActivateDefaultTimeAgoStatic()
    {
        Yii::$app->params['formatter']['timeAgoStatic'] = true;
        $ts = time() - (172800 + 10);
        $result = TimeAgo::widget(['timestamp' => $ts]);
        $this->assertTimeAgoNotActive($result);
    }

    public function testActivateTimeAgoStaticInWidget()
    {
        Yii::$app->params['formatter']['timeAgoStatic'] = false;
        $ts = time() - (172800 + 10);
        $result = TimeAgo::widget(['timestamp' => $ts, 'staticTimeAgo' => true]);
        $this->assertTimeAgoNotActive($result);
    }

    public function testDefaultSettingsTimeAgoBeforeActiveOutOfInterval()
    {
        // TS outside of default timAgoBefore (172800 s)
        $ts = time() - (172800 + 10);
        $result = TimeAgo::widget(['timestamp' => $ts]);
        $this->assertTimeAgoNotActive($result);
    }

    public function testDefaultSettingsTimeAgoBeforeNotActiveOutOfInterval()
    {
        // TS outside of default 172800 but default deactivated
        Yii::$app->params['formatter']['timeAgoBefore'] = false;
        $ts = time() - (172800 + 10);
        $result = TimeAgo::widget(['timestamp' => $ts]);
        $this->assertTimeAgoActive($result);
    }

    public function testHideTimeAfterMatches()
    {
        // TS outside of default 172800 but default deactivated
        $ts = DateTime::createFromFormat('Y-m-d H:i:s', '2018-10-12 12:00:00')->getTimestamp();
        $result = TimeAgo::widget(['timestamp' => $ts]);
        $this->assertStringContainsString('Oct 12, 2018</time>', $result);
    }

    public function testHideTimeAfterNotMatches()
    {
        // TS outside of default 172800 but default deactivated
        $ts = (new DateTime())->setTime(12,00,00)->getTimestamp();
        $result = TimeAgo::widget(['timestamp' => $ts, 'timeAgoBefore' => 1]);
        $this->assertStringContainsString('12:00 PM</time>', $result);
    }

    public function testHideTimeAfterDeactivated()
    {
        // TS outside of default 172800 but default deactivated
        $ts = DateTime::createFromFormat('Y-m-d H:i:s', '2018-10-12 12:00:00')->getTimestamp();
        $result = TimeAgo::widget(['timestamp' => $ts, 'hideTimeAfter' => false]);
        $this->assertStringContainsString('Oct 12, 2018 - 12:00 PM</time>', $result);
    }

    private function assertTimeAgoActive($result)
    {
        $this->assertStringContainsString('data-ui-addition="timeago"', $result);
    }

    private function assertTimeAgoNotActive($result)
    {
        $this->assertStringNotContainsString('data-ui-addition="timeago"', $result);
    }

}
