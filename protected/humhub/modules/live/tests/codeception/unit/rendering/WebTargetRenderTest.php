<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering;

use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\notification\targets\WebTarget;

class WebTargetRenderTest extends HumHubDbTestCase
{

    use Specify;

    public function testDefaultView()
    {
        $notification = notifications\TestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->send(User::findOne(['id' => 1]));

        $target = Yii::$app->notification->getTarget(WebTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertContains('New', $result);
        $this->assertContains('<h1>TestedMailViewNotificationHTML</h1>', $result);
    }

    public function testOverwriteViewFile()
    {
        $notification = notifications\TestNotification::instance()->about(Post::findOne(['id' => 1]));
        $notification->send(User::findOne(['id' => 1]));
        $notification->viewName = 'special';
        $notification->saveRecord(User::findOne(['id' => 1]));
        $target = Yii::$app->notification->getTarget(WebTarget::class);
        $renderer = $target->getRenderer();
        $result = $renderer->render($notification);
        $this->assertContains('New', $result);
        $this->assertContains('<div>Special:<h1>TestedMailViewNotificationHTML</h1></div>', $result);
    }

}
