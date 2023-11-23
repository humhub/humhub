<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\models\File;
use tests\codeception\_support\HumHubDbTestCase;

class RichTextEmailHtmlConverterTest extends HumHubDbTestCase
{
    public function testConvertLinkToHtml()
    {
        $this->assertConversionResult(
            'Test[Link](https://www.humhub.com/de)Test',
            '<p>Test<a href="https://www.humhub.com/de" target="_blank" rel="nofollow noreferrer noopener"> Link </a>Test</p>');
    }

    public function testConvertLinkAsTextToHtml()
    {
        $this->assertConversionResult(
            'Test[Link](https://www.humhub.com/de)Test',
            '<p>Test Link Test</p>', [
                RichTextToHtmlConverter::OPTION_LINK_AS_TEXT => true,
            ]);
    }

    public function testConvertImageToHtml()
    {
        $admin = $this->becomeUser('Admin');

        $file = new File();
        $file->file_name = 'test_image.jpg';
        $file->save();

        $token = DownloadAction::generateDownloadToken($file, $admin);

        $this->assertConversionResult(
            'Test![' . $file->file_name . '](file-guid:' . $file->guid . ' "' . $file->file_name . '")Test',
            '<p>Test<img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $file->guid . '&amp;hash_sha1=&amp;token=' . $token . '" alt="test_image.jpg" style="max-width: 100%;">Test</p>', [
            RichTextToEmailHtmlConverter::OPTION_RECEIVER_USER => $admin,
        ]);

        $this->assertConversionResult(
            '![](http://local/image.jpg)',
            '<p><img src="http://local/image.jpg" alt="" style="max-width: 100%;"></p>');

        $this->assertConversionResult(
            '![Alt text><](http://local/image.jpg "Description text" =200x100)',
            '<p><img class="center-block" src="http://local/image.jpg" width="200" height="100" alt="Alt text" title="Description text" style="max-width: 100%; display: block; margin: auto;"></p>');
    }

    public function testConvertImageAlt()
    {
        $this->assertConversionResult(
            '![Image <alt> "text"](http://local/image.jpg)',
            '<p><img src="http://local/image.jpg" alt="Image &lt;alt&gt; &quot;text&quot;" style="max-width: 100%;"></p>');
    }

    public function testConvertImageDescription()
    {
        $this->assertConversionResult(
        '![](http://local/image.jpg "Image <description> "text"")',
            '<p><img src="http://local/image.jpg" alt="" title="Image &lt;description&gt; &quot;text&quot;" style="max-width: 100%;"></p>');
    }

    public function testConvertImageAlignment()
    {
        $this->assertConversionResult(
            '![alt>](http://local/image.jpg "desc")',
            '<p><img class="pull-right" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; float: right;"></p>');

        $this->assertConversionResult(
            '![alt<](http://local/image.jpg "desc")',
            '<p><img class="pull-left" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; float: left;"></p>');

        $this->assertConversionResult(
            '![alt><](http://local/image.jpg "desc")',
            '<p><img class="center-block" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; display: block; margin: auto;"></p>');
    }

    public function testConvertImageSize()
    {
        $this->assertConversionResult(
            '![alt](http://local/image.jpg =100x)',
            '<p><img src="http://local/image.jpg" width="100" alt="alt" style="max-width: 100%;"></p>');

        $this->assertConversionResult(
            '![alt](http://local/image.jpg =x200)',
            '<p><img src="http://local/image.jpg" height="200" alt="alt" style="max-width: 100%;"></p>');

        $this->assertConversionResult(
            '![alt](http://local/image.jpg =50x120)',
            '<p><img src="http://local/image.jpg" width="50" height="120" alt="alt" style="max-width: 100%;"></p>');
    }

    private function assertConversionResult($markdown, $expected = null, $options = [])
    {
        if ($expected === null) {
            $expected = $markdown;
        }

        $result = RichTextToEmailHtmlConverter::process($markdown, $options);

        $expected = trim(str_replace(["\n", "\r"], '', $expected));
        $result = trim(str_replace(["\n", "\r"], '', $result));

        static::assertEquals($expected, $result);
    }

}
