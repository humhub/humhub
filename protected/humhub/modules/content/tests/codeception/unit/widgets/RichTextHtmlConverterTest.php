<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
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
    public function testConvertLinkToHtml()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            '<p>Test <a href="https://www.humhub.com/de" target="_blank" rel="nofollow noreferrer noopener">Link</a></p>');
    }

    public function testConvertLinkWithCustomTargetToHtml()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            '<p>Test <a href="https://www.humhub.com/de" target="_self" rel="nofollow noreferrer noopener">Link</a></p>',
            [RichTextToHtmlConverter::OPTION_LINK_TARGET => '_self']);
    }

    public function testConvertLinkAsText()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            '<p>Test Link</p>',
            [RichTextToHtmlConverter::OPTION_LINK_AS_TEXT => '_self']);
    }

    public function testConvertLinkWithoutTargetToHtml()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de)',
            '<p>Test <a href="https://www.humhub.com/de">Link</a></p>',
            [RichTextToHtmlConverter::OPTION_PREV_LINK_TARGET => true]);
    }


    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithTitleToHtml()
    {
        $this->assertConversionResult(
            'Test [Link](https://www.humhub.com/de "Link Title")',
            '<p>Test <a href="https://www.humhub.com/de" target="_blank" title="Link Title" rel="nofollow noreferrer noopener">Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertLinkWithSpecialCharToHtml()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](https://www.humhub.com/de)',
            '<p>Test <a href="https://www.humhub.com/de" target="_blank" rel="nofollow noreferrer noopener">Link &amp;&lt; Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkToHtml()
    {
        $this->assertConversionResult(
            'Test [Link](/p/site)',
            '<p>Test <a href="http://localhost/p/site" target="_blank" rel="nofollow noreferrer noopener">Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeLinkWithSpecialCharToHtml()
    {
        $this->assertConversionResult(
            'Test [Link &< Link](/p/site)',
            '<p>Test <a href="http://localhost/p/site" target="_blank" rel="nofollow noreferrer noopener">Link &amp;&lt; Link</a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testEmptyLinkLabelToHtml()
    {
        $this->assertConversionResult(
            'Test [](/p/site)',
            '<p>Test <a href="http://localhost/p/site" target="_blank" rel="nofollow noreferrer noopener"></a></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testLinkWithMarkedText()
    {
        $this->assertConversionResult(
            'Test [**Bold** Link](http://localhost/p/site)',
            '<p>Test <a href="http://localhost/p/site" target="_blank" rel="nofollow noreferrer noopener"><strong>Bold</strong> Link</a></p>');
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
            '<p>Test <a href="mailto:test@test.com" target="_blank" rel="noreferrer noopener">Test Mail</a></p>');
    }

    /*
     * Images
     */

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageToHtml()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            '<p>Test <img src="https://www.humhub.com/static/img/logo.png" alt="Alt Text"></p>');
    }

    public function testConvertImageAsLink()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            '<p>Test <a href="https://www.humhub.com/static/img/logo.png" target="_blank" rel="nofollow noreferrer noopener">Alt Text</a></p>',
            [RichTextToHtmlConverter::OPTION_IMAGE_AS_LINK => true]);
    }

    public function testConvertImageWithoutTitleAsLink()
    {
        $this->assertConversionResult(
            'Test ![](https://www.humhub.com/static/img/logo.png)',
            '<p>Test <a href="https://www.humhub.com/static/img/logo.png" target="_blank" rel="nofollow noreferrer noopener">https://www.humhub.com/static/img/logo.png</a></p>',
            [RichTextToHtmlConverter::OPTION_IMAGE_AS_LINK => true]);
    }

    public function testConvertImageAsText()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](https://www.humhub.com/static/img/logo.png)',
            '<p>Test Alt Text</p>',
            [RichTextToHtmlConverter::OPTION_IMAGE_AS_LINK => true, RichTextToHtmlConverter::OPTION_LINK_AS_TEXT => true]);
    }

    public function testConvertImageWithoutTitleAsText()
    {
        $this->assertConversionResult(
            'Test ![](https://www.humhub.com/static/img/logo.png)',
            '<p>Test https://www.humhub.com/static/img/logo.png</p>',
            [RichTextToHtmlConverter::OPTION_IMAGE_AS_LINK => true, RichTextToHtmlConverter::OPTION_LINK_AS_TEXT => true]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSpecialCharToHtml()
    {
        $this->assertConversionResult(
            'Test ![Alt &< Text](https://www.humhub.com/static/img/logo.png)',
            '<p>Test <img src="https://www.humhub.com/static/img/logo.png" alt="Alt &amp;&lt; Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageToHtml()
    {
        $this->assertConversionResult(
            'Test ![Alt Text](/static/img/logo.png)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Alt Text"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertRelativeImageWithSpecialCharToHtml()
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
    public function testConvertImageWithSizeToHtml()
    {
        // Image size currently not supported in html output
        $this->assertConversionResult(
            'Test ![Scaled Image](http://localhost/static/img/logo.png "img6.jpg" =150x)',
            '<p>Test <img src="http://localhost/static/img/logo.png" alt="Scaled Image" title="img6.jpg"></p>');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testConvertImageWithSizeAndNoTitleToHtml()
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
    public function testMentioningToHtml()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertConversionResult(
            'Test mention ' . MentioningExtension::buildMentioning($user),
            '<p>Test mention <a href="http://localhost/index-test.php?r=user%2Fprofile&amp;cguid=01e50e0d-82cd-41fc-8b0c-552392f5839c" target="_blank" rel="nofollow noreferrer noopener">@Admin Tester</a></p>');
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
            '<p>Test file <a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz&amp;hash_sha1=xxx" target="_blank" rel="nofollow noreferrer noopener">Test File</a></p>');
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
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz&amp;hash_sha1=xxx" alt="Test File"></p>');
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
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz&amp;hash_sha1=xxx" alt="Test File"></p>');
    }

    public function testDataImage()
    {
        // DATA images currently not supported
        $this->assertConversionResult(
            '![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==)',
            '<p></p>'
        );
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
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz&amp;hash_sha1=xxx" alt="Test File"></p>');
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
            '<p>Test file <img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=xyz&amp;hash_sha1=xxx" alt="Test File"></p>');
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
            '<p><a href="https://www.youtube.com/watch?v=xxxy" target="_blank" rel="nofollow noreferrer noopener">https://www.youtube.com/watch?v=xxxy</a></p>');
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
        $expected .= "<ol>".PHP_EOL ."";
        $expected .= "<li>First Element</li>".PHP_EOL ."";
        $expected .= "<li>Second Element</li>".PHP_EOL ."";
        $expected .= "</ol>";

        $this->assertConversionResult(
            "This is a list\n\n1. First Element\n2. Second Element",
            $expected);
    }

    public function testConvertUnorderedList()
    {
        $expected = "<p>This is a list</p>".PHP_EOL ."";
        $expected .= "<ul>".PHP_EOL ."";
        $expected .= "<li>First Element</li>".PHP_EOL ."";
        $expected .= "<li>Second Element</li>".PHP_EOL ."";
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedSubList()
    {
        $expected = "<p>This is a list</p>".PHP_EOL;
        $expected .= "<ul>".PHP_EOL;
        $expected .= "<li>First Element<ul>".PHP_EOL;
        $expected .= "<li>First Sub Element</li>".PHP_EOL;
        $expected .= "</ul>".PHP_EOL;
        $expected .= "</li>".PHP_EOL;
        $expected .= "<li>Second Element</li>".PHP_EOL;
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedMultipleSubItems()
    {
        $expected = "<p>This is a list</p>".PHP_EOL;
        $expected .= "<ul>".PHP_EOL;
        $expected .= "<li>First Element<ul>".PHP_EOL;
        $expected .= "<li>First Sub Element</li>".PHP_EOL;
        $expected .= "<li>Second Sub Element</li>".PHP_EOL;
        $expected .= "</ul>".PHP_EOL;
        $expected .= "</li>".PHP_EOL;
        $expected .= "<li>Second Element</li>".PHP_EOL;
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n   - Second Sub Element\n- Second Element",
            $expected);
    }

    public function testConvertUnorderedMultipleLevelSubItems()
    {
        $expected = "<p>This is a list</p>".PHP_EOL;
        $expected .= "<ul>".PHP_EOL;
        $expected .= "<li>First Element<ul>".PHP_EOL;
        $expected .= "<li>First Sub Element<ul>".PHP_EOL;
        $expected .= "<li>Second <strong>Level Sub</strong> Element</li>".PHP_EOL;
        $expected .= "</ul>".PHP_EOL;
        $expected .= "</li>".PHP_EOL;
        $expected .= "</ul>".PHP_EOL;
        $expected .= "</li>".PHP_EOL;
        $expected .= "<li>Second Element</li>".PHP_EOL;
        $expected .= "</ul>";

        $this->assertConversionResult(
            "This is a list\n\n- First Element\n   - First Sub Element\n      - Second **Level Sub** Element\n- Second Element",
            $expected);
    }

    /*
    * Tables
    */
    public function testConvertTableWithAlignment()
    {
        $expected = "<table>".PHP_EOL ."<thead>".PHP_EOL ."<tr><th>Tables</th><th align=\"center\">Are</th><th align=\"right\">Cool</th></tr>".PHP_EOL ."</thead>".PHP_EOL;
        $expected .= "<tbody>".PHP_EOL ."<tr><td>col 3 is</td><td align=\"center\">right-aligned</td><td align=\"right\">$1600</td></tr>".PHP_EOL ."</tbody>".PHP_EOL;
        $expected .= "</table>";

        $this->assertConversionResult(
            "| Tables | Are | Cool |\n| ------------- |:-------------:| -----:|\n| col 3 is | right-aligned | $1600 |",
            $expected);
    }

   /*
    * Tables
    */

    /**
     * @skip see https://github.com/cebe/markdown/issues/179
     */
    public function testConvertTableWithoutBodyAtEnd()
    {
        $expected = "<table>".PHP_EOL ."<thead>".PHP_EOL ."<tr><th>Tables</th><th>Are</th><th>Cool</th></tr>".PHP_EOL ."</thead>".PHP_EOL;
        $expected .= "<tbody>".PHP_EOL ."</tbody>".PHP_EOL;
        $expected .= "</table>";

        $this->assertConversionResult(
            "| Tables | Are | Cool |\n| ------------- | ------------- | ----- |",
            $expected);
    }

    /**
     * @skip see https://stackoverflow.com/questions/57800619/htmlpurifier-keeps-removing-my-tables-what-is-the-right-config
     * Tables without tbody are not allowed in HTML spec
     */
    public function testConvertTableWithoutTd()
    {
        $expected = "<table>".PHP_EOL ."<thead>".PHP_EOL ."<tr><th>Tables</th><th>Are</th><th>Cool</th></tr>".PHP_EOL ."</thead>".PHP_EOL;
        $expected .= "<tbody>".PHP_EOL ."</tbody>".PHP_EOL;
        $expected .= "</table>";

        $this->assertConversionResult(
            "| Tables | Are | Cool |\n| ------------- | ------------- | ----- |\n",
            $expected);
    }

    public function testConvertExcludeTable()
    {
        $this->assertConversionResult(
            "| Tables | Are | Cool |\n| ------------- | ------------- | ----- |\n| col 3 is | right-aligned | $1600 |",
            '', ['exclude' => ['table']]);
    }

    public function testConvertTableWithInlineMark()
    {
        $expected = "<table>".PHP_EOL ."<thead>".PHP_EOL ."<tr><th>Tables</th><th>Are</th><th>Cool</th></tr>".PHP_EOL ."</thead>".PHP_EOL;
        $expected .= "<tbody>".PHP_EOL ."<tr><td>col 3 is</td><td><strong>right</strong>-aligned</td><td>$1600</td></tr>".PHP_EOL ."</tbody>".PHP_EOL;
        $expected .= "</table>";

        $this->assertConversionResult(
            "Tables | Are | Cool  |\n| ------------- | ------------- | ----- |\n| col 3 is | **right**-aligned | $1600 |",
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
            "<blockquote><p>This is a quote</p>".PHP_EOL ."</blockquote>");
    }

    public function testConvertBlockNestedQuote()
    {
        $this->assertConversionResult(
            "> This is a quote \n>\n> > within a quote",
            "<blockquote><p>This is a quote </p>".PHP_EOL ."<blockquote><p>within a quote</p>".PHP_EOL ."</blockquote>".PHP_EOL ."</blockquote>");
    }

    /*
     * Code block
     */
    public function testConvertBlockCodeBlock()
    {
        $this->assertConversionResult(
            "```\nThis is a code block\n```",
            "<pre><code>This is a code block".PHP_EOL ."</code></pre>");
    }

    public function testConvertBlockCodeBlockWithLanguage()
    {
        $this->assertConversionResult(
            "```html\n<b>This is a code block</b>\n```",
            "<pre><code class=\"language-html\">&lt;b&gt;This is a code block&lt;/b&gt;".PHP_EOL ."</code></pre>");
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

    /*
    * Hard break
    */

    public function testHardBreak()
    {
        $this->assertConversionResult(
            "Test\\\nBreak",
            "<p>Test<br>".PHP_EOL ."Break</p>");
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
            "<p>&lt;div&gt;This is <br>".PHP_EOL ." html&lt;/div&gt;</p>");
    }

    public function testParagraph()
    {
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2",
            "<p>Paragraph1</p>".PHP_EOL ."<p>Paragraph2</p>");
    }

    /*
     * new line seperation of blocks
     */
    public function testParagraphs()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\nParagraph2\n\nParagraph3 with\\\nnew line",
            "<p>Paragraph1</p>".PHP_EOL ."<p>Paragraph2</p>".PHP_EOL ."<p>Paragraph3 with<br>".PHP_EOL ."new line</p>");
    }

    public function testCodeBlockAfterParagraph()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "Paragraph1\n\n```\ncode block\n```",
            "<p>Paragraph1</p>".PHP_EOL ."<pre><code>code block".PHP_EOL ."</code></pre>");
    }

    public function testParagraphAfterCodeBlock()
    {
        // Tags are not stripped since the richtext does not support html and interprets html as normal text
        $this->assertConversionResult(
            "```\ncode block\n```\n\nParagraph1",
            "<pre><code>code block".PHP_EOL ."</code></pre>".PHP_EOL ."<p>Paragraph1</p>");
    }

    public function testCachedResult()
    {
        $this->assertConversionResult('TestXY', '<p>TestXY</p>', [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
        $this->assertConversionResult('', '<p>TestXY</p>', [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
    }

    public function testMixedConverterCachedResult()
    {
        $this->assertConversionResult('TestXY', '<p>TestXY</p>', [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
        $test = RichText::convert('TestXY', RichText::FORMAT_PLAINTEXT, [RichTextToHtmlConverter::OPTION_CACHE_KEY => 'myResult']);
        static::assertEquals('TestXY', $test);
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
        if ($expected === null) {
            $expected = $markdown;
        }

        $result = RichText::convert($markdown, RichText::FORMAT_HTML, $options);

        // Currently relative image
        static::assertEquals(
            trim(str_replace(["\n", "\r"], '', $expected)),
            trim(str_replace(["\n", "\r"], '', $result)));
    }

}
