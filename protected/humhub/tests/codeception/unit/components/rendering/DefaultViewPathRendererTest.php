<?php

namespace humhub\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use humhub\components\rendering\DefaultViewPathRenderer;

class DefaultViewPathRendererTest extends HumHubDbTestCase
{

    use Specify;

    public function testSimpleDefaultView()
    {
        $viewable = new TestViewable(['viewName' => 'nonExistent']);
        $renderer = new DefaultViewPathRenderer(['defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php']);
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }
    
    public function testDefaultPathView()
    {
        $viewable = new TestViewable(['viewName' => 'parent2']);
        $renderer = new DefaultViewPathRenderer([
            'defaultViewPath' => '@tests/codeception/unit/components/rendering/views',
            'defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php'
            ]);
        $this->assertEquals('<h1>ParentView2:TestTitle</h1>', $renderer->render($viewable));
    }
    
    public function testViewFoundView()
    {
        $viewable = new TestViewable(['viewName' => 'testView']);
        $renderer = new DefaultViewPathRenderer([
            'defaultViewPath' => '@tests/codeception/unit/components/rendering/views',
            'defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php'
            ]);
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }
    
    public function testViewFoundSettingsView()
    {
        $viewable = new TestViewable(['viewName' => 'mail']);
        $renderer = new DefaultViewPathRenderer([
            'parent' => true,
            'subPath' => 'mails',
            'defaultViewPath' => '@tests/codeception/unit/components/rendering/views',
            'defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php'
            ]);
        $this->assertEquals('<h1>MailView:TestTitle</h1>', $renderer->render($viewable));
    }
}
