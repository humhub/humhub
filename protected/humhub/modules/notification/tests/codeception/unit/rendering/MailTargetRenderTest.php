<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\notification\components\MailNotificationTarget;

class MailTargetRenderTest extends HumHubDbTestCase
{
    use Specify;
    
    public function testDefaultView()
    {
        $notification = notifications\TestedMailViewNotification::instance();
        $target = Yii::$app->notification->getTarget(MailNotificationTarget::class);
        $renderer = $target->getRenderer();
        $this->assertContains('<h1>TestedMailViewNotificationHTML</h1>', $renderer->render($notification));
        $this->assertContains('TestedMailViewNotificationText', $renderer->renderText($notification));
    }

    public function testOverwriteViewFile()
    {
        $notification = notifications\TestedMailViewNotification::instance();
        $notification->viewName = 'special';
        $target = Yii::$app->notification->getTarget(MailNotificationTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertContains('<div>Special:<h1>TestedMailViewNotificationHTML</h1></div>', $result);
        $this->assertContains('<div>MyLayout:', $result);
        $this->assertContains('TestedMailViewNotificationText', $renderer->renderText($notification));
    }
}
