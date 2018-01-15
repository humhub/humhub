<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use humhub\components\rendering\ViewPathRenderer;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\ViewNotFoundException;

class ViewPathRendererTest extends HumHubDbTestCase
{
    /**
     * @throws ViewNotFoundException
     */
    public function testSimpleViewPathRenderer()
    {
        $viewable = new TestViewable(['viewName' => 'testView']);
        $renderer = new ViewPathRenderer();
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
    public function testViewPathRendererSuffix()
    {
        $viewable = new TestViewable(['viewName' => 'testView.php']);
        $renderer = new ViewPathRenderer();
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
    public function testSetViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'view2']);
        $renderer = new ViewPathRenderer(['viewPath' => '@tests/codeception/unit/components/rendering/lib/views2']);
        $this->assertEquals('<h1>TestTitle</h1>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
    public function testParentSettingViewPath()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new ViewPathRenderer(['parent' => true]);
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
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
        } catch (ViewNotFoundException $ex) {
            $this->assertTrue(true);
        }
    }
}
