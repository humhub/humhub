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


class RichTextHtmlConverterTest extends HumHubDbTestCase
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
            '<p>Test <a href="https://www.humhub.com/de">Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithTitleToText()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de "Link Title")',
            '<p>Test <a href="https://www.humhub.com/de" title="Link Title">Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](https://www.humhub.com/de)',
            '<p>Test <a href="https://www.humhub.com/de">Link &amp;&lt; Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkToText()
    {
        $this->assertConversionResult(
            'Test [Link](/p/site)',
            '<p>Test <a href="http://localhost/p/site">Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](/p/site)',
            '<p>Test <a href="http://localhost/p/site">Link &amp;&lt; Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testEmptyLinkLabelToText()
    {
        $this->assertConversionResult(
            'Test [](/p/site)',
            '<p>Test <a href="http://localhost/p/site"></a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testLinkWithMarkedText()
    {
        $this->assertConversionResult(
            'Test [**Bold** Link](http://localhost/p/site)',
            '<p>Test <a href="http://localhost/p/site"><strong>Bold</strong> Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkifiedLink()
    {
        $this->assertConversionResult(
            'Test http://localhost/p/site',
            "<p>Test http://localhost/p/site</p>");
    }

    public function testConvertMailtoLink()
    {
        $this->assertConversionResult(
            'Test [Test Mail](mailto:test@test.com)',
            '<p>Test <a href="mailto:test@test.com">Test Mail</a></p>');
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
            '<p>Test <img src="https://www.humhub.com/static/img/logo.png" alt="Alt Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test ![Alt &< Text](https://www.humhub.com/static/img/logo.png)',
            '<p>Test <img src="https://www.humhub.com/static/img/logo.png" alt="Alt &amp;&lt; Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageToText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](/static/img/logo.png)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Alt Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageWithSpecialCharToText()
    {
        $this->assertConversionResult(
            'Test ![Alt & < Text](/static/img/logo.png)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Alt &amp; &lt; Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithTitleText()
    {
        $this->assertConversionResult(
            'Test ![Image Alt](http://localhost/static/img/logo.png "Image Title")',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Image Alt" title="Image Title"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeToText()
    {
        // Image size currently not supported in html output
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png "img6.jpg" =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image" title="img6.jpg"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeAndNoTitleToText()
    {
        // Image size currently not supported in html output
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentRight()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image>](http://localhost/static/img/logo.png =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentLeft()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image<](http://localhost/static/img/logo.png =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithImageAlignmentCenter()
    {
        $this->assertConversionResult(
            'Test ![Scaled Image><](http://localhost/static/img/logo.png =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image"></p>');
    }

    /*
     * Hard break
     */

    public function testHardBreak()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "<p>Test<br>\r\nBreak</p>");
    }

    /*
     * Emoji
     */

    public function testConvertEmojiToUtf8Text()
    {
        $this->assertConversionResult(
            "Test emoji :smile:",
            "<p>Test emoji " . EmojiMap::MAP['smile'] . "</p>");
    }

    /*
     * Mentioning
     */
    public function testMentioningToText()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            '<p>Test mention <a href="http://localhost/index-test.php?r=user%2Fprofile&amp;cguid=01e50e0d-82cd-41fc-8b0c-552392f5839c">Admin Tester</a></p>');
    }

    public function testMentionNotFound()
    {
        $this->assertConversionResult(
            'Test non existing mention [Non Existing](mention:xyz "...")',
            "<p>Test non existing mention @Non Existing</p>");
    }

    public function testMentionInActiveUser()
    {
        $user = User::findOne(['id' => 2]);
        $user->updateAttributes(['status' => User::STATUS_DISABLED]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            "<p>Test mention @" . $user->getDisplayName() . "</p>");
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
            'title' => 'Test File',
            'mime_type' => 'text/plain',
            'size' => 302176
        ]);

        static::assertTrue($file->save());
        $this->assertConversionResult(
            'Test file [Test File](file-guid:xyz)',
            '<p>Test file <a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz">Test File</a></p>');
    }

    public function testFileNotFound()
    {
        $this->assertConversionResult(
            'Test file [Test File](file-guid:doesNotExist)',
            "<p>Test file Test File</p>");
    }

    public function testImageFileA()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        static::assertTrue($file->save());
        $this->assertConversionResult(
            'Test file ![Test File](file-guid:xyz)',
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz" alt="Test File"></p>');
    }

    public function testImageFileWithRightAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        static::assertTrue($file->save());
        $this->assertConversionResult(
            'Test file ![Test File>](file-guid:xyz)',
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz" alt="Test File"></p>');
    }

    public function testImageFileWithLeftAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        static::assertTrue($file->save());
        $this->assertConversionResult(
            'Test file ![Test File<](file-guid:xyz)',
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz" alt="Test File"></p>');
    }

    public function testImageFileWithCenterAlign()
    {
        $file = new File([
            'guid' => 'xyz',
            'object_model' => Post::class,
            'object_id' => 1,
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);

        static::assertTrue($file->save());
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:xyz)',
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz" alt="Test File"></p>');
    }

    public function testImageFileNotFound()
    {
        $this->assertConversionResult(
            'Test file ![Test File><](file-guid:doesNotExist)',
            "<p>Test file Test File</p>");
    }


    /*
     * oembed extension
     */
    public function testConvertOembed()
    {
        $this->assertConversionResult(
            '[https://www.youtube.com/watch?v=xxxy](oembed:https://www.youtube.com/watch?v=xxxy)',
            '<p><a href="https://www.youtube.com/watch?v=xxxy">https://www.youtube.com/watch?v=xxxy</a></p>');
    }

    /*
     * marks
     */
    public function testConvertMarkBold1()
    {
        $this->assertConversionResult(
            'This is **bold**',
            "<p>This is <strong>bold</strong></p>");
    }

    public function testConvertMarkBold2()
    {
        $this->assertConversionResult(
            'This is __bold__',
            "<p>This is <strong>bold</strong></p>");
    }

    public function testConvertMarkItalic1()
    {
        $this->assertConversionResult(
            'This is _italic_',
            "<p>This is <em>italic</em></p>");
    }

    public function testConvertMarkItalic2()
    {
        $this->assertConversionResult(
            'This is *italic*',
            "<p>This is <em>italic</em></p>");
    }

    public function testConvertMarkInlineCode()
    {
        $this->assertConversionResult(
            'This is `inline code`',
            "<p>This is <code>inline code</code></p>");
    }

    public function testConvertMarkStrike()
    {
        $this->assertConversionResult(
            'This is ~~strikethrough text~~',
            "<p>This is <del>strikethrough text</del></p>");
    }

    /*
     * Lists
     */
    public function testConvertOrderedList()
    {
        $expected = "<p>This is a list</p>\r\n";
        $expected .= "<ol>\r\n";
        $expected .= "<li>First Element</li>\r\n";
        $expected .= "<li>Second Element</li>\r\n";
        $expected .= "</ol>";

        $this->assertConversionResult(
            "This is a list\n\n1. First Element\n2. Second Element",
            $expected);
    }

    public function testConvertUnorderedList()
    {
        $expected = "<p>This is a list</p>\r\n";
        $expected .= "<ul>\r\n";
        $expected .= "<li>First Element</li>\r\n";
        $expected .= "<li>Second Element</li>\r\n";
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedSubList()
    {
        $expected = "<p>This is a list</p>\r\n";
        $expected .= "<ul>\r\n";
        $expected .= "<li>First Element<ul>\r\n";
        $expected .= "<li>First Sub Element</li>\r\n";
        $expected .= "</ul>\r\n";
        $expected .= "</li>\r\n";
        $expected .= "<li>Second Element</li>\r\n";
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedMultipleSubItems()
    {
        $expected = "<p>This is a list</p>\r\n";
        $expected .= "<ul>\r\n";
        $expected .= "<li>First Element<ul>\r\n";
        $expected .= "<li>First Sub Element</li>\r\n";
        $expected .= "<li>Second Sub Element</li>\r\n";
        $expected .= "</ul>\r\n";
        $expected .= "</li>\r\n";
        $expected .= "<li>Second Element</li>\r\n";
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n   - Second Sub Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedMultipleLevelSubItems()
    {
        $expected = "<p>This is a list</p>\r\n";
        $expected .= "<ul>\r\n";
        $expected .= "<li>First Element<ul>\r\n";
        $expected .= "<li>First Sub Element<ul>\r\n";
        $expected .= "<li>Second <strong>Level Sub</strong> Element</li>\r\n";
        $expected .= "</ul>\r\n";
        $expected .= "</li>\r\n";
        $expected .= "</ul>\r\n";
        $expected .= "</li>\r\n";
        $expected .= "<li>Second Element</li>\r\n";
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n      - Second **Level Sub** Element\n- Second Element",
            $expected);
    }

    /*
    * Tables
    */
    public function testConvertTableA()
    {
        $expected = "<table>\r\n<thead>\r\n<tr><th>Tables </th><th>Are </th><th>Cool</th></tr>\r\n</thead>\r\n";
        $expected .= "<tbody>\r\n<tr><td>col 3 is </td><td>right-aligned </td><td>$1600</td></tr>\r\n</tbody>\r\n";
        $expected .= "</table>";

        $this->assertConversionResult(
            "| Tables | Are | Cool |\n| ------------- |:-------------:| -----:|\n| col 3 is | right-aligned | $1600 |",
            $expected);
    }

    public function testConvertTableWithInlineMark()
    {
        $expected = "<table>\r\n<thead>\r\n<tr><th>Tables </th><th>Are </th><th>Cool</th></tr>\r\n</thead>\r\n";
        $expected .= "<tbody>\r\n<tr><td>col 3 is </td><td><strong>right</strong>-aligned </td><td>$1600</td></tr>\r\n</tbody>\r\n";
        $expected .= "</table>";

        $this->assertConversionResult(
            "Tables | Are | Cool  |\n| ------------- |:-------------:| -----:|\n| col 3 is | **right**-aligned | $1600 |",
            $expected);
    }


    /*
     * encoding
     */

    public function testConvertSpecialCharacters()
    {
        $this->assertConversionResult(
            "Test special chars like & or <test>",
            "<p>Test special chars like &amp; or &lt;test&gt;</p>");
    }

    /*
     * Quote
     */
    public function testConvertBlockQuote()
    {
        $this->assertConversionResult(
            "> This is a quote",
            "<blockquote><p>This is a quote</p>\r\n</blockquote>");
    }

    public function testConvertBlockNestedQuote()
    {
        $this->assertConversionResult(
            "> This is a quote > within a quter",
            "<blockquote><p>This is a quote &gt; within a quter</p>\r\n</blockquote>");
    }

    /*
     * Code block
     */
    public function testConvertBlockCodeBlock()
    {
        $this->assertConversionResult(
            "```\nThis is a code block\n```",
            "<pre><code>This is a code block\r\n</code></pre>");
    }

    public function testConvertBlockCodeBlockWithLanguage()
    {
        $this->assertConversionResult(
            "```html\n<b>This is a code block</b>\n```",
            "<pre><code class=\"language-html\">&lt;b&gt;This is a code block&lt;/b&gt;\r\n</code></pre>");
    }

    /*
    * Headline
    */

    public function testConvertBlockHeadline()
    {
        $this->assertConversionResult(
            "# First order headline",
            "<h1>First order headline</h1>");
    }

    public function testConvertBlockHeadlineSecondLevel()
    {
        $this->assertConversionResult(
            "## First order headline",
            "<h2>First order headline</h2>");
    }

    /*
    * Html
    */
    public function testConvertHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is html</div>",
            "<p>&lt;div&gt;This is html&lt;/div&gt;</p>");
    }

    public function testConvertHtmlBlockInHeadline()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "## <div>This is html</div>",
            "<h2>&lt;div&gt;This is html&lt;/div&gt;</h2>");
    }

    public function testConvertInlineHtml()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <em>bold text</em>",
            "<p>This is &lt;em&gt;bold text&lt;/em&gt;</p>");
    }

    public function testHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "This is <br> was a hard line break",
            "<p>This is <br> was a hard line break</p>");
    }

    public function testMultipleHtmlBreak()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            'This is <br> was a hard <br /> line break',
            "<p>This is <br> was a hard <br> line break</p>");
    }

    public function testConvertBreakInHtmlBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "<div>This is <br> html</div>",
            "<p>&lt;div&gt;This is <br>\r\n html&lt;/div&gt;</p>");
    }

    public function testParagraph()
    {
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2",
            "<p>Paragraph1</p>\r\n<p>Paragraph2</p>");
    }

    /*
     * new line seperation of blocks
     */
    public function testParagraphs()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2\n\nParagraph3 with\\\nnew line",
            "<p>Paragraph1</p>\r\n<p>Paragraph2</p>\r\n<p>Paragraph3 with<br>\r\nnew line</p>");
    }

    public function testCodeBlockAfterParagraph()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\n```\ncode block\n```",
            "<p>Paragraph1</p>\r\n<pre><code>code block\r\n</code></pre>");
    }

    public function testParagraphAfterCodeBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "```\ncode block\n```\n\nParagraph1",
            "<pre><code>code block\r\n</code></pre>\r\n<p>Paragraph1</p>");
    }



    /*
     * HR
     */

    public function testConvertHR()
    {
        $this->assertConversionResult(
            "---",
            "<hr>");
    }

    private function assertConversionResult($markdown, $expected = null, $options = [])
    {
        if (!$expected) {
            $expected = $markdown;
        }

        $result = RichText::convert($markdown, RichText::FORMAT_HTML, $options);

        // Currently relative image
        static::assertEquals($expected, trim($result));
    }

}
