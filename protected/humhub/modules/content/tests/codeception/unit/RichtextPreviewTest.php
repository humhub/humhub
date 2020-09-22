<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;


class RichtextPreviewTest extends HumHubDbTestCase
{

    public function testStripHtml()
    {
        $this->assertEquals('Test', RichText::preview('<b>Test</b>'));
    }

    public function testNewLine()
    {
        $this->assertEquals('Te\\nst', RichText::preview('Te\\nst'));
    }

    public function testAmpEntity()
    {
        $this->assertEquals('test &amp; test', RichText::preview('test & test'));
        $this->assertEquals('test & test', html_entity_decode(RichText::preview('test & test'), ENT_QUOTES, 'UTF-8'));
    }

    public function testQuoteEntity()
    {
        $this->assertEquals('test &#039; test', RichText::preview('test \' test'));
        $this->assertEquals('test \' test', html_entity_decode(RichText::preview('test \' test'), ENT_QUOTES, 'UTF-8'));
    }

    public function testSimpleLink()
    {
        $this->assertEquals('Test: Github', RichText::preview('Test: [Github](https://github.com)'));
    }

    public function testEmoji()
    {
        $this->assertEquals('Test: 😃', RichText::preview('Test: :smiley:'));
    }

    public function testMentioning()
    {
        $this->assertEquals('Test: Admin Tester', RichText::preview('Test: [Admin Tester](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "/humhub/develop/index.php?r=user%2Fprofile&cguid=01e50e0d-82cd-41fc-8b0c-552392f5839c")'));
    }
}
