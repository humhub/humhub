<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\RichText;
use tests\codeception\_support\HumHubDbTestCase;


class RichTextPlaintextConverterTest extends HumHubDbTestCase
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkToText()
    {
        $text = 'Test [Link](https://www.humhub.com/de)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Link(https://www.humhub.com/de)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharToText()
    {
        $text = 'Test [Link &< Link](https://www.humhub.com/de)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Link &< Link(https://www.humhub.com/de)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkToText()
    {
        $text = 'Test [Link](/p/site)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Link(http://localhost/p/site)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkWithSpecialCharToText()
    {
        $text = 'Test [Link &< Link](/p/site)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Link &< Link(http://localhost/p/site)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageToText()
    {
        $text = 'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Alt Text(https://www.humhub.com/static/img/logo.png)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSpecialCharToText()
    {
        $text = 'Test ![Alt & < Text](https://www.humhub.com/static/img/logo.png)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        static::assertEquals("Test Alt & < Text(https://www.humhub.com/static/img/logo.png)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageToText()
    {
        $text = 'Test ![Alt Text](/static/img/logo.png)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals("Test Alt Text(http://localhost/static/img/logo.png)\n\n", $result);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageWithSpecialCharToText()
    {
        $text = 'Test ![Alt & < Text](/static/img/logo.png)';
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals("Test Alt & < Text(http://localhost/static/img/logo.png)\n\n", $result);
    }

    public function testConvertMultiLineToText()
    {
        $text = "Test ![Alt & < Text](/static/img/logo.png) \n This is another line";
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals("Test Alt & < Text(http://localhost/static/img/logo.png) \n This is another line\n\n", $result);
    }

    public function testConvertDoubleLineBreakToText()
    {
        $text = "Paragraph1\n\nParagraph2";
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals("Paragraph1\n\nParagraph2\n\n", $result);
    }

    public function testConvertEmojiToUtf8Text()
    {
        $text = "Test emoji :smile:";
        $result = RichText::convert($text, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals("Test emoji ".EmojiMap::MAP['smile']."\n\n", $result);
    }
}
