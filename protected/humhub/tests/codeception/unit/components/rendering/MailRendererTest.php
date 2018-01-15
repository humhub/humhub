<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use humhub\components\rendering\MailRenderer;
use humhub\tests\codeception\unit\components\rendering\lib\TestViewable;
use tests\codeception\_support\HumHubDbTestCase;

class MailRendererTest extends HumHubDbTestCase
{
    public function testExistingTextView()
    {
        $viewable = new TestViewable(['viewName' => 'testView']);
        $renderer = new MailRenderer([
            'parent' => true,
            'defaultTextView' => '@tests/codeception/unit/components/rendering/lib/views',
            'defaultTextViewPath' => '@tests/codeception/unit/components/rendering/lib/views/specialView.php'
        ]);
        $this->assertEquals('TextView:TestTitle', $renderer->renderText($viewable));
    }

    public function testNonExistingTextView()
    {
        $viewable = new TestViewable(['viewName' => 'nonExisting']);
        $renderer = new MailRenderer([
            'parent' => true,
            'defaultTextView' => '@tests/codeception/unit/components/rendering/lib/views/testView.php',
            'defaultTextViewPath' => '@tests/codeception/unit/components/rendering/lib/views'
        ]);
        $this->assertEquals('TestTitle', $renderer->renderText($viewable));
    }

    public function testExistingViewPathTextView()
    {
        $viewable = new TestViewable(['viewName' => 'specialView']);
        $renderer = new MailRenderer([
            'parent' => true,
            'defaultTextView' => '@tests/codeception/unit/components/rendering/lib/views/testView.php',
            'defaultTextViewPath' => '@tests/codeception/unit/components/rendering/lib/views'
        ]);
        $this->assertEquals('SpecialView', $renderer->renderText($viewable));
    }
}
