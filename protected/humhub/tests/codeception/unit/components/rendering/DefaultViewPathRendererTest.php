<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use humhub\components\rendering\DefaultViewPathRenderer;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\ViewNotFoundException;

class DefaultViewPathRendererTest extends HumHubDbTestCase
{
    /**
     * @throws ViewNotFoundException
     */
    public function testSimpleDefaultView()
    {
        $viewable = new TestViewable(['viewName' => 'nonExistent']);
        $renderer = new DefaultViewPathRenderer(
            ['defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php']
        );
        $this->assertEquals('<h1>ParentView:TestTitle</h1>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
    public function testDefaultPathView()
    {
        $viewable = new TestViewable(['viewName' => 'parent2']);
        $renderer = new DefaultViewPathRenderer([
            'defaultViewPath' => '@tests/codeception/unit/components/rendering/views',
            'defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php'
        ]);
        $this->assertEquals('<h1>ParentView2:TestTitle</h1>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
    public function testViewFoundView()
    {
        $viewable = new TestViewable(['viewName' => 'testView']);
        $renderer = new DefaultViewPathRenderer([
            'defaultViewPath' => '@tests/codeception/unit/components/rendering/views',
            'defaultView' => '@tests/codeception/unit/components/rendering/views/parent.php'
        ]);
        $this->assertEquals('<div>TestTitle</div>', $renderer->render($viewable));
    }

    /**
     * @throws ViewNotFoundException
     */
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
