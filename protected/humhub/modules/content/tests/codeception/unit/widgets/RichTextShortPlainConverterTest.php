<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Tests for the plain text variant of the short text converter
 * ([[RichTextToShortTextConverter]] / FORMAT_SHORT_TEXT).
 *
 * Plain text output must not be HTML encoded.
 */
class RichTextShortPlainConverterTest extends HumHubDbTestCase
{
    public function testKeepsAmpersand()
    {
        $this->assertEquals(
            'test & test',
            RichText::convert('test & test', RichText::FORMAT_SHORT_TEXT),
        );
    }

    public function testKeepsAngleBrackets()
    {
        $this->assertEquals(
            'This is <em>bold text</em>',
            RichText::convert('This is <em>bold text</em>', RichText::FORMAT_SHORT_TEXT),
        );
    }

    public function testKeepsSingleQuote()
    {
        $this->assertEquals(
            "test ' test",
            RichText::convert("test ' test", RichText::FORMAT_SHORT_TEXT),
        );
    }

    public function testConvertsImage()
    {
        $this->assertEquals(
            '[Image]',
            RichText::convert('![Alt](file-guid-image.png)', RichText::FORMAT_SHORT_TEXT),
        );
    }
}
