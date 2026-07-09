<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\converter\RichTextToShortHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Tests that the dedicated short HTML converter and the deprecated
 * FORMAT_SHORTTEXT alias both yield HTML encoded results.
 */
class RichTextShortHtmlConverterTest extends HumHubDbTestCase
{
    public function testEncodesSpecialChars()
    {
        $this->assertEquals(
            'test &amp; test',
            RichText::convert('test & test', RichText::FORMAT_SHORT_HTML),
        );
    }

    public function testEncodesAngleBrackets()
    {
        $this->assertEquals(
            'This is &lt;em&gt;bold text&lt;/em&gt;',
            RichText::convert('This is <em>bold text</em>', RichText::FORMAT_SHORT_HTML),
        );
    }

    public function testNl2BrOption()
    {
        $this->assertEquals(
            "Test<br>\nBreak",
            RichText::convert("Test\\\nBreak", RichText::FORMAT_SHORT_HTML, [
                RichTextToShortTextConverter::OPTION_PRESERVE_SPACES => true,
                RichTextToShortHtmlConverter::OPTION_NL2BR => true,
            ]),
        );
    }

    public function testDeprecatedFormatStillEncodes()
    {
        // FORMAT_SHORTTEXT is deprecated but must still produce encoded HTML for BC.
        $this->assertEquals(
            'test &amp; test',
            RichText::convert('test & test', RichText::FORMAT_SHORTTEXT),
        );
    }
}
