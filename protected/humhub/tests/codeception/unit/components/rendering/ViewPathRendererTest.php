<?php

namespace humhub\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use humhub\components\rendering\ViewPathRenderer;

class ViewPathRendererTest extends HumHubDbTestCase
{

    use Specify;

    public function testSimpleViewPathRenderer()
    {
        $viewable = new TestViewable(['viewName' => 'testView']);
        $renderer = new ViewPathRenderer();
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }
    
    public function testViewPathRendererSuffix()
    {
        $viewable = new TestViewable(['viewName' => 'testView.php']);
        $renderer = new ViewPathRenderer();
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }
    
    public function testSetViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'view2']);
        $renderer = new ViewPathRenderer(['viewPath' => '@tests/codeception/unit/components/rendering/lib/views2']);
        $this->assertEquals('<h1>TestTitle</h1>', $renderer->render($viewable));
    }
    
    public function testParentSettingViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new ViewPathRenderer(['parent' => true]);
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }
    
    public function testSubPathSettingViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'mail']);
        $renderer = new ViewPathRenderer(['parent' => true, 'subPath' => 'mails']);
        $this->assertEquals('<h1>MailView:TestTitle</h1>', $renderer->render($viewable));
    }
    
    public function testNonExistingViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'nonExisting']);
        $renderer = new ViewPathRenderer(['parent' => true, 'subPath' => 'mails']);
        
        try {
             $renderer->render($viewable);
             $this->assertTrue(false);
        } catch (\yii\base\ViewNotFoundException $ex) {
            $this->assertTrue(true);
        }
    }
}
