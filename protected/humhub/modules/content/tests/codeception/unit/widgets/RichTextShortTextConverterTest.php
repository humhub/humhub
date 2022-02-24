<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\EmojiMap;
use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;


class RichTextShortTextConverterTest extends HumHubDbTestCase
{
    /*
     * Links
     */

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkToShortText()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            "Test Link");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertTextWithMaxLength()
    {
        $this->assertConversionResult(
            'Test **text** truncation',
            "Test...", ['maxLength' => 5]);
    }

    public function testConvertMultiByteTextWithMaxLength()
    {
        $this->assertConversionResult(
            '相*ウ*ヨ報<br />夫チエ**景東署**シイ連苦ワ径特サニコワ政深ちぎ見敗ぜあじも内庫ゅしづぽ児意泉ねッを黒能わぱふ緩昇ろじ帯北悩びぞば。',
            "相ウヨ報 夫チエ景東...", [RichTextToShortTextConverter::OPTION_MAX_LENGTH => 10]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertMultiParagraphWithMaxLength()
    {
        $this->assertConversionResult(
            "Test **bold text** truncation\n\nNew Paragraph",
            "Test bold text truncation New...", ['maxLength' => 30]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharToShortText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](https://www.humhub.com/de)',
            "Test Link &amp;&lt; Link");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkToShortText()
    {
        $this->assertConversionResult(
            'Test [Link](/p/site)',
            "Test Link");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkWithSpecialCharToShortText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](/p/site)',
            "Test Link &amp;&lt; Link");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testEmptyLinkLabelToShortText()
    {
        $this->assertConversionResult(
            'Test [](/p/site)',
            "Test");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testInvalidProtocolLinkToShortText()
    {
        $this->assertConversionResult(
            'Test [Invalid Url](javascript:alert(1))',
            "Test Invalid Url");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testLinkWithMarkedText()
    {
        $this->assertConversionResult(
            'Test [**Bold** Url](http://localhost/p/site)',
            "Test Bold Url");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkifiedLink()
    {
        $this->assertConversionResult(
            'Test http://localhost/p/site',
            "Test http://localhost/p/site");
    }

    public function testConvertMailtoLink()
    {
        $this->assertConversionResult(
            'Test [Test Mail](mailto:test@test.com)',
            'Test Test Mail');
    }

    /*
     * Images
     */

    public function testConvertImageAsLink()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageToShortText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSpecialCharToShortText()
    {
        $this->assertConversionResult(
            'Test ![Alt & < Text](https://www.humhub.com/static/img/logo.png)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageToShortText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](/static/img/logo.png)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageWithSpecialCharToShortText()
    {
        $this->assertConversionResult(
            'Test ![Alt & < Text](/static/img/logo.png)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithTitleText()
    {
        $this->assertConversionResult(
            'Test ![Image Label](http://localhost/static/img/logo.png "Image Title")',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeToShortText()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png "img6.jpg" =150x)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeAndNoTitleToShortText()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png =150x)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentRight()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image>](http://localhost/static/img/logo.png =150x)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentLeft()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image<](http://localhost/static/img/logo.png =150x)',
            'Test [Image]');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentCenter()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image><](http://localhost/static/img/logo.png =150x)',
            'Test [Image]');
    }

    /*
     * Paragraph
     */

    public function testParagraph()
    {
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2",
            "Paragraph1 Paragraph2");
    }

    /*
     * Emoji
     */

    public function testConvertEmojiToUtf8Text()
    {
        $this->assertConversionResult(
            "Test emoji :smile:",
            "Test emoji " . EmojiMap::MAP['smile'] . "");
    }

    /*
     * Mentioning
     */
    public function testMentioningToShortText()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            "Test mention @" . $user->getDisplayName());
    }

    public function testMentionNotFound()
    {
        $this->assertConversionResult(
            'Test non existing mention [Non Existing](mention:xyz "...")',
            "Test non existing mention @Non Existing");
    }

    public function testMentionInActiveUser()
    {
        $user = User::findOne(['id' => 2]);
        $user->updateAttributes(['status' => User::STATUS_DISABLED]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            "Test mention @" . $user->getDisplayName() . "");
    }

    public function testMentionEmptyText()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertConversionResult(
            'Test mention [](mention:' . $user->guid . ')',
            "Test mention @" . $user->getDisplayName());
    }

    /*
    * file-guid extension
    */
    public function testFileGuidText()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.txt',
            'hash_sha1' => 'xxx',
            'title' => 'Test File',
            'mime_type' => 'text/plain',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $this->assertConversionResult(
            'Test file [Test File](file-guid:xyz)',
            "Test file Test File");
    }

    public function testFileNotFound()
    {
        $this->assertConversionResult(
            'Test file [Test File](file-guid:doesNotExist)',
            "Test file Test File");
    }

    public function testImageFile()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'hash_sha1' => 'xxx',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $this->assertConversionResult(
            'Test file ![Test File](file-guid:xyz)',
            'Test file [Image]');
    }

    public function testImageFileWithRightAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'hash_sha1' => 'xxx',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $this->assertConversionResult(
            'Test file ![Test File>](file-guid:xyz)',
            'Test file [Image]');
    }

    public function testImageFileWithLeftAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'hash_sha1' => 'xxx',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $this->assertConversionResult(
            'Test file ![Test File<](file-guid:xyz)',
            'Test file [Image]');
    }

    public function testImageFileWithCenterAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'hash_sha1' => 'xxx',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e) {
            // Need to catch since hash saving will fail
        }
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:xyz)',
            'Test file [Image]');
    }

    public function testImageFileNotFound()
    {
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:doesNotExist)',
            'Test file [Image]');
    }

    public function testDataImage()
    {
        // DATA images currently not supported
        $this->assertConversionResult(
            '![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==)',
            '[Image]'
        );
    }


    /*
     * oembed extension
     */
    public function testConvertOembed()
    {
        $this->assertConversionResult(
            '[https://www.youtube.com/watch?v=xxxy](oembed:https://www.youtube.com/watch?v=xxxy)',
            'https://www.youtube.com/watch?v=xxxy');
    }

    /*
     * marks
     */
    public function testConvertMarkBold1()
    {
        $this->assertConversionResult(
            'This is **bold**',
            "This is bold");
    }

    public function testConvertMarkBold2()
    {
        $this->assertConversionResult(
            'This is __bold__',
            "This is bold");
    }

    public function testConvertMarkItalic1()
    {
        $this->assertConversionResult(
            'This is _italic_',
            "This is italic");
    }

    public function testConvertMarkItalic2()
    {
        $this->assertConversionResult(
            'This is *italic*',
            "This is italic");
    }

    public function testConvertMarkInlineCode()
    {
        $this->assertConversionResult(
            'This is `inline code`',
            "This is inline code");
    }

    public function testConvertMarkStrike()
    {
        $this->assertConversionResult(
            'This is ~~strikethrough text~~',
            "This is strikethrough text");
    }

    /*
     * Lists
     */
    public function testConvertOrderedList()
    {
        $this->assertConversionResult(
            "This is a list\n\n1. First Element\n2. Second Element",
            "This is a list 1. First Element 2. Second Element");
    }

    public function testConvertOrderedSubList()
    {
        $this->assertConversionResult(
            "This is a list\n\n1 First Element\n   1 First Sub Element\n2 Second Element",
            "This is a list 1 First Element 1 First Sub Element 2 Second Element");
    }

    public function testConvertOrderedMultipleSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n1 First Element\n   1 First Sub Element\n   2 Second Sub Element\n2 Second Element",
            "This is a list 1 First Element 1 First Sub Element 2 Second Sub Element 2 Second Element");
    }

    public function testConvertUnorderedList()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n- Second Element",
            "This is a list - First Element - Second Element");
    }

    public function testConvertUnorderedSubList()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n- Second Element",
            "This is a list - First Element - First Sub Element - Second Element");
    }

    public function testConvertUnorderedMultipleSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n   - Second Sub Element\n- Second Element",
            "This is a list - First Element - First Sub Element - Second Sub Element - Second Element");
    }

    public function testConvertUnorderedMultipleLevelSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n      - Second **Level Sub** Element\n- Second Element",
            "This is a list - First Element - First Sub Element - Second Level Sub Element - Second Element");
    }

    /*
    * Tables
    */
    public function testConvertTable()
    {
        $this->assertConversionResult(
            "| Tables        | Are           | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is      | right-aligned | $1600 |"
            , "[Table]");
    }

    public function testConvertTableWithInlineMark()
    {
        $this->assertConversionResult(
            "| Tables        | Are           | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is      | **right**-aligned | $1600 |",
            "[Table]");
    }

    /*
     * encoding
     */

    public function testConvertSpecialCharacters()
    {
        $this->assertConversionResult(
            "Test special chars like & or <test>>",
            "Test special chars like &amp; or &lt;test&gt;&gt;");
    }

    /*
     * Quote
     */

    public function testConvertBlockQuote()
    {
        $this->assertConversionResult(
            "> This is a quote",
            "This is a quote");
    }

    public function testConvertBlockNestedQuote()
    {
        $this->assertConversionResult(
            "> This is a quote \n>\n> > within a quote",
            "This is a quote within a quote");
    }

    /*
     * Code block
     */
    public function testConvertBlockCodeBlock()
    {
        $this->assertConversionResult(
            "```\n<b>This is a code block</b>\n```",
            "[Code Block]");
    }

    public function testConvertBlockCodeBlockWithLanguage()
    {
        $this->assertConversionResult(
            "```html\n<b>This is a code block</b>\n```",
            "[Code Block]");
    }

    /*
    * Headline
    */

    public function testConvertBlockHeadline()
    {
        $this->assertConversionResult(
            "# First order headline",
            "First order headline");
    }

    public function testConvertBlockHeadlineSecondLevel()
    {
        $this->assertConversionResult(
            "## First order headline",
            "First order headline");
    }

    /*
    * Html
    */
    public function testConvertHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is html</div>",
            "&lt;div&gt;This is html&lt;/div&gt;");
    }

    public function testConvertInlineHtml()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <em>bold text</em>",
            "This is &lt;em&gt;bold text&lt;/em&gt;");
    }

    /*
     * Hard Break
     */

    public function testHardBreak()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "Test Break");
    }

    public function testHardBreakWithPreserveOption()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "Test\nBreak", [RichTextToShortTextConverter::OPTION_PRESERVE_SPACES => true]);
    }

    public function testHardBreakWithPreserveAndNL2BROption()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "Test<br>\nBreak", [
                RichTextToShortTextConverter::OPTION_PRESERVE_SPACES => true,
                RichTextToShortTextConverter::OPTION_NL2BR => true
            ]);
    }

    public function testHardBreakWithoutNewLine()
    {
        $this->assertConversionResult(
            "Test\\\n",
            "Test");
    }

    public function testHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <br> was a hard line break",
            "This is was a hard line break");
    }

    public function testHtmlBreakWithPreserveOption()
    {
        $this->assertConversionResult(
            "This is <br> was a hard line break",
            "This is \n was a hard line break", [RichTextToShortTextConverter::OPTION_PRESERVE_SPACES => true]);
    }

    public function testMultipleHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            'This is <br> was a hard <br /> line break',
            "This is was a hard line break");
    }

    public function testConvertBreakInHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is <br> html</div>",
            "&lt;div&gt;This is html&lt;/div&gt;");
    }

    /*
     * new line seperation of blocks
     */
    public function testParagraphs()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2\n\nParagraph3 with\nnew line",
            "Paragraph1 Paragraph2 Paragraph3 with new line");
    }

    public function testCodeBlockAfterParagraph()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\n```\ncode block\n```",
            "Paragraph1 [Code Block]");
    }

    public function testParagraphAfterCodeBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "```\ncode block\n```\n\nParagraph1",
            "[Code Block] Paragraph1");
    }

    /*
     * HR
     */

    public function testConvertHR()
    {
        $this->assertConversionResult(
            "---",
            "");
    }


    public function testCache()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "```\ncode block\n```\n\nParagraph1",
            "[Code Block] Paragraph1", [RichTextToShortTextConverter::OPTION_CACHE_KEY => 'test1']);

        $this->assertConversionResult(
            "IgnoreSinceCached...",
            "[Code Block] Paragraph1", [RichTextToShortTextConverter::OPTION_CACHE_KEY => 'test1']);
    }

    public function testCacheWithDifferentMaxLength()
    {

        $this->assertConversionResult(
            "This **is a long** text we will truncate",
            "This is a...", [
            RichTextToShortTextConverter::OPTION_CACHE_KEY => 'test2',
            RichTextToShortTextConverter::OPTION_MAX_LENGTH => 9
            ]);

        $this->assertConversionResult(
            "IgnoreSinceCached...",
            "This is a long text...", [
            RichTextToShortTextConverter::OPTION_CACHE_KEY => 'test2',
            RichTextToShortTextConverter::OPTION_MAX_LENGTH => 19
            ]);
    }

    public function testMixedConverterCachedResult()
    {
        $this->assertConversionResult('TestXY', 'TestXY', [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
        $test = RichText::convert('ShouldNotBeCached', RichText::FORMAT_PLAINTEXT, [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
        static::assertEquals('ShouldNotBeCached', $test);
    }

    private function assertConversionResult($markdown, $expected = null, $options = [])
    {
        if ($expected === null) {
            $expected = $markdown;
        }

        $result = RichText::convert($markdown, RichText::FORMAT_SHORTTEXT, $options);
        // Currently relative image
        static::assertEquals($expected, $result);
    }

}
