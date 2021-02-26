<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;


class RichTextPlaintextConverterTest extends HumHubDbTestCase
{
    /*
     * Links
     */

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkToText()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            "Test Link(https://www.humhub.com/de)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](https://www.humhub.com/de)',
            "Test Link &< Link(https://www.humhub.com/de)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkToText()
    {
        $this->assertConversionResult(
            'Test [Link](/p/site)',
            "Test Link(http://localhost/p/site)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](/p/site)',
            "Test Link &< Link(http://localhost/p/site)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testEmptyLinkLabelToText()
    {
        $this->assertConversionResult(
            'Test [](/p/site)',
            "Test (http://localhost/p/site)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testInvalidProtocolLinkToText()
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
            "Test Bold Url(http://localhost/p/site)");
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
            'Test Test Mail(mailto:test@test.com)');
    }

    /*
     * Images
     */

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageToText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            "Test Alt Text(https://www.humhub.com/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test ![Alt & < Text](https://www.humhub.com/static/img/logo.png)',
            "Test Alt & < Text(https://www.humhub.com/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageToText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](/static/img/logo.png)',
            "Test Alt Text(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test ![Alt & < Text](/static/img/logo.png)',
            "Test Alt & < Text(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithTitleText()
    {
        $this->assertConversionResult(
            'Test ![Image Label](http://localhost/static/img/logo.png "Image Title")',
            "Test Image Label(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeToText()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png "img6.jpg" =150x)',
            "Test Scaled Image(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeAndNoTitleToText()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png =150x)',
            "Test Scaled Image(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentRight()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image>](http://localhost/static/img/logo.png =150x)',
            "Test Scaled Image(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentLeft()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image<](http://localhost/static/img/logo.png =150x)',
            "Test Scaled Image(http://localhost/static/img/logo.png)");
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentCenter()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image><](http://localhost/static/img/logo.png =150x)',
            "Test Scaled Image(http://localhost/static/img/logo.png)");
    }

    /*
     * Hard break
     */
    public function testHardBreak()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "Test\nBreak");
    }

    /*
     * Paragraph
     */

    public function testParagraph()
    {
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2",
            "Paragraph1\n\nParagraph2");
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
    public function testMentioningToText()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            "Test mention @" . $user->getDisplayName() . "(" . $user->createUrl(null, [], true) . ")");
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
            "Test mention @" . $user->getDisplayName() . "(" . $user->createUrl(null, [], true) . ")");
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
        } catch (\Throwable $e ) {
            // Need to catch since hash saving will fail
        }

        $this->assertConversionResult(
            'Test file [Test File](file-guid:xyz)',
            "Test file Test File(" . $file->getUrl(null, true) . ")");
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
        } catch (\Throwable $e ) {
            // Need to catch since hash saving will fail
        }
        $this->assertConversionResult(
            'Test file ![Test File](file-guid:xyz)',
            "Test file Test File(" . $file->getUrl(null, true) . ")");
    }

    public function testDataImage()
    {
        // DATA images currently not supported
        $this->assertConversionResult(
            '![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==)',
            ''
        );
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
        } catch (\Throwable $e ) {
            // Need to catch since hash saving will fail
        }
        $this->assertConversionResult(
            'Test file ![Test File>](file-guid:xyz)',
            "Test file Test File(" . $file->getUrl(null, true) . ")");
    }

    public function testImageFileWithLeftAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'hash_sha1' => 'xxx',
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        try {
            $file->save();
        } catch (\Throwable $e ) {
            // Need to catch since hash saving will fail
        }
        $this->assertConversionResult(
            'Test file ![Test File<](file-guid:xyz)',
            "Test file Test File(" . $file->getUrl(null, true) . ")");
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
        } catch (\Throwable $e ) {
            // Need to catch since hash saving will fail
        }
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:xyz)',
            "Test file Test File(" . $file->getUrl(null, true) . ")");
    }

    public function testImageFileNotFound()
    {
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:doesNotExist)',
            "Test file Test File");
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
            "This is a list\n\n1. First Element\n2. Second Element");
    }

    public function testConvertOrderedSubList()
    {
        $this->assertConversionResult(
            "This is a list\n\n1 First Element\n   1 First Sub Element\n2 Second Element",
            "This is a list\n\n1 First Element\n   1 First Sub Element\n2 Second Element");
    }

    public function testConvertOrderedMultipleSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n1 First Element\n   1 First Sub Element\n   2 Second Sub Element\n2 Second Element",
            "This is a list\n\n1 First Element\n   1 First Sub Element\n   2 Second Sub Element\n2 Second Element");
    }

    public function testConvertUnorderedList()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n- Second Element",
            "This is a list\n\n- First Element\n- Second Element");
    }

    public function testConvertUnorderedSubList()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n- Second Element",
            "This is a list\n\n- First Element\n   - First Sub Element\n- Second Element");
    }

    public function testConvertUnorderedMultipleSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n   - Second Sub Element\n- Second Element",
            "This is a list\n\n- First Element\n   - First Sub Element\n   - Second Sub Element\n- Second Element");
    }

    public function testConvertUnorderedMultipleLevelSubItems()
    {
        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n      - Second **Level Sub** Element\n- Second Element",
            "This is a list\n\n- First Element\n   - First Sub Element\n      - Second Level Sub Element\n- Second Element");
    }

    /*
    * Tables
    */
    public function testConvertTable()
    {
        $this->assertConversionResult(
            "| Tables        | Are           | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is      | right-aligned | $1600 |");
    }

    public function testConvertTableWithInlineMark()
    {
        $this->assertConversionResult(
            "| Tables        | Are           | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is      | **right**-aligned | $1600 |",
            "| Tables        | Are           | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is      | right-aligned | $1600 |");
    }


    /*
     * encoding
     */

    public function testConvertSpecialCharacters()
    {
        $this->assertConversionResult(
            "Test special chars like & or <test>>",
            "Test special chars like & or <test>>");
    }

    /*
     * Quote
     */

    public function testConvertBlockQuote()
    {
        $this->assertConversionResult(
            "> This is a quote",
            "> This is a quote");
    }

    public function testConvertBlockNestedQuote()
    {
        $this->assertConversionResult(
            "> This is a quote \n>\n> > within a quote",
            "> This is a quote \n\n> within a quote");
    }

    /*
     * Code block
     */
    public function testConvertBlockCodeBlock()
    {
        $this->assertConversionResult(
            "```\n<b>This is a code block</b>\n```",
            "```\n<b>This is a code block</b>\n```");
    }

    public function testConvertBlockCodeBlockWithLanguage()
    {
        $this->assertConversionResult(
            "```html\n<b>This is a code block</b>\n```",
            "```html\n<b>This is a code block</b>\n```");
    }

    /*
    * Headline
    */

    public function testConvertBlockHeadline()
    {
        $this->assertConversionResult(
            "# First order headline",
            "# First order headline");
    }

    public function testConvertBlockHeadlineSecondLevel()
    {
        $this->assertConversionResult(
            "## First order headline",
            "## First order headline");
    }

    /*
    * Html
    */
    public function testConvertHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is html</div>",
            "<div>This is html</div>");
    }

    public function testConvertInlineHtml()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <em>bold text</em>",
            "This is <em>bold text</em>");
    }

    public function testHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <br> was a hard line break",
            "This is \n was a hard line break");
    }

    public function testMultipleHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            'This is <br> was a hard <br /> line break',
            "This is \n was a hard \n line break");
    }

    public function testConvertBreakInHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is <br> html</div>",
            "<div>This is \n html</div>");
    }

    /*
     * new line seperation of blocks
     */
    public function testParagraphs()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2\n\nParagraph3 with\nnew line",
            "Paragraph1\n\nParagraph2\n\nParagraph3 with\nnew line");
    }

    public function testCodeBlockAfterParagraph()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\n```\ncode block\n```",
            "Paragraph1\n\n```\ncode block\n```");
    }

    public function testParagraphAfterCodeBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "```\ncode block\n```\n\nParagraph1",
            "```\ncode block\n```\n\nParagraph1");
    }



    /*
     * HR
     */

    public function testConvertHR()
    {
        $this->assertConversionResult(
            "---",
            "----------------------------------------");
    }

    private function assertConversionResult($markdown, $expected = null)
    {
        if ($expected === null) {
            $expected = $markdown;
        }

        $result = RichText::convert($markdown, RichText::FORMAT_PLAINTEXT);
        // Currently relative image
        static::assertEquals($expected, $result);
    }

}
