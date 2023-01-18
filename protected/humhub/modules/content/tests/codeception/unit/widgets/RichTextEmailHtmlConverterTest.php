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
            '<p>Test<img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $file->guid . '&amp;hash_sha1=&amp;token=' . $token . '" alt="test_image.jpg">Test</p>', [
            RichTextToEmailHtmlConverter::OPTION_RECEIVER_USER => $admin,
        ]);
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
