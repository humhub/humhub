<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;

/**
 * Tests the genuinely unencoded short text conversion (FORMAT_SHORT_TEXT /
 * RichTextToShortTextConverter). The result must not be HTML encoded — for the
 * encoded preview (FORMAT_SHORT_HTML, legacy FORMAT_SHORTTEXT) see
 * RichTextShortTextConverterTest.
 */
class RichTextToShortTextConverterTest extends HumHubDbTestCase
{
    /**
     * @throws InvalidConfigException
     */
    public function testSpecialCharsRemainUnencoded()
    {
        $this->assertConversionResult(
            "Test special chars like & or <test>>",
            "Test special chars like & or <test>>",
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharRemainsUnencoded()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](https://www.humhub.com/de)',
            "Test Link &< Link",
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public function testConvertTextWithMaxLength()
    {
        $this->assertConversionResult(
            'Test **text** truncation',
            "Test...",
            ['maxLength' => 5],
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public function testShortHtmlFormatStaysEncoded()
    {
        static::assertEquals(
            "Test Link &amp;&lt; Link",
            RichText::convert('Test [Link &< Link](https://www.humhub.com/de)', RichText::FORMAT_SHORT_HTML),
        );
    }

    /**
     * @throws InvalidConfigException
     */
    private function assertConversionResult($markdown, $expected = null, $options = [])
    {
        $result = RichText::convert($markdown, RichText::FORMAT_SHORT_TEXT, $options);

        static::assertEquals($expected ?? $markdown, $result);
    }
}
