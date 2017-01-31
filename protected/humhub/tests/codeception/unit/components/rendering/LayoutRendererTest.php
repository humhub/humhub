<?php

namespace humhub\tests\codeception\unit;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use humhub\components\rendering\LayoutRenderer;

class LayoutRendererTest extends HumHubDbTestCase
{

    use Specify;

    public function testSimpleViewPathRenderer()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new LayoutRenderer(['parent' => true, 'layout' => '@tests/codeception/unit/components/rendering/views/layouts/testLayout.php']);
        $this->assertEquals('<div>TestLayout:<h1>ParentView:TestTitle</h1></div>', $renderer->render($viewable));
    }
    
    public function testNoLayout()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new LayoutRenderer(['parent' => true]);
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }
}
