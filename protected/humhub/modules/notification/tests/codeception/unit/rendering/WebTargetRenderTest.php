<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\notification\components\WebNotificationTarget;

class WebTargetRenderTest extends HumHubDbTestCase
{

    use Specify;

    public function testDefaultView()
    {
        $notification = notifications\TestedMailViewNotification::instance();
        $target = Yii::$app->notification->getTarget(WebNotificationTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertContains('New', $result);
        $this->assertContains('<h1>TestedMailViewNotificationHTML</h1>', $result);
    }

    public function testOverwriteViewFile()
    {
        $notification = notifications\TestedMailViewNotification::instance();
        $notification->viewName = 'special';
        $target = Yii::$app->notification->getTarget(WebNotificationTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertContains('New', $result);
        $this->assertContains('<div>Special:<h1>TestedMailViewNotificationHTML</h1></div>', $result);
    }
    
    public function testOverwriteLayoutFile()
    {
        $notification = notifications\TestedMailViewNotification::instance();
        $notification->viewName = 'specialLayout';
        $target = Yii::$app->notification->getTarget(WebNotificationTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertNotContains('New', $result);
        $this->assertEquals('<div>MyLayout:<h1>TestedMailViewNotificationHTML</h1></div>', trim($result));
    }
}
