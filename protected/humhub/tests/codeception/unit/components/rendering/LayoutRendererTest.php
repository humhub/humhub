<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use humhub\components\rendering\LayoutRenderer;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use tests\codeception\_support\HumHubDbTestCase;

class LayoutRendererTest extends HumHubDbTestCase
{
    public function testSimpleViewPathRenderer()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new LayoutRenderer([
            'parent' => true,
            'layout' => '@tests/codeception/unit/components/rendering/views/layouts/testLayout.php'
        ]);
        $this->assertEquals('<div>TestLayout:<h1>ParentView:TestTitle</h1></div>', $renderer->render($viewable));
    }

    public function testNoLayout()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new LayoutRenderer(['parent' => true]);
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }
}
