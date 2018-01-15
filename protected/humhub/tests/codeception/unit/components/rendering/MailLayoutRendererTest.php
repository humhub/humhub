<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use humhub\components\rendering\MailLayoutRenderer;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use tests\codeception\_support\HumHubDbTestCase;

class MailLayoutRendererTest extends HumHubDbTestCase
{
    public function testExistingTextView()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new MailLayoutRenderer(
            [
                'parent' => true,
                'textLayout' => '@tests/codeception/unit/components/rendering/views/layouts/testLayout.php'
            ]
        );
        $this->assertEquals('TestLayout:TestViewText', $renderer->renderText($viewable));
    }

    public function testNoLayoutRenderText()
    {
        $viewable = new TestViewable(['viewName' => 'parent']);
        $renderer = new MailLayoutRenderer(['parent' => true]);
        $this->assertEquals('TestViewText', $renderer->renderText($viewable));
    }

    public function testNonExistingTextLayout()
    {
        try {
            $viewable = new TestViewable(['viewName' => 'nonExisting']);
            $renderer = new MailLayoutRenderer(
                ['textLayout' => '@tests/codeception/unit/components/rendering/views/layouts/nonExsting.php']
            );
            $renderer->renderText($viewable);
            $this->assertTrue(false);
        } catch (\yii\base\ViewNotFoundException $ex) {
            $this->assertTrue(true);
        }
    }
}
