<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\notification\targets\MailTarget;

class MailTargetRenderTest extends HumHubDbTestCase
{

    use Specify;

    public function testDefaultView()
    {
        $notification = notifications\TestNotification::instance();
        $target = Yii::$app->notification->getTarget(MailTarget::class);
        $renderer = $target->getRenderer();
        $this->assertStringContainsString('<h1>TestedMailViewNotificationHTML</h1>', $renderer->render($notification));
        $this->assertStringContainsString('TestedMailViewNotificationText', $renderer->renderText($notification));
    }

}
