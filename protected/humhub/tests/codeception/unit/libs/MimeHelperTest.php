<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\libs\MimeHelper;
use humhub\modules\file\models\File;

/**
 * Class MimeHelperTest
 */
class MimeHelperTest extends Unit
{
    /**
     * Test valid extension
     * @dataProvider dataValidExtension
     */
    public function testValidExtension($expected, $actual)
    {
        $this->assertEquals($expected, MimeHelper::getMimeIconClassByExtension($actual));
    }

    /**
     * Test valid extension
     * @dataProvider dataValidExtension
     */
    public function testValidExtensionFileObject($expected, $actual)
    {
        $temp = new File();
        $temp->file_name = uniqid() . '.' . $actual;

        $this->assertEquals($expected, MimeHelper::getMimeIconClassByExtension($temp));
    }

    /**
     * Testcases for valid extension
     * @return array
     */
    public function dataValidExtension()
    {
        return [
            'word' => [MimeHelper::ICON_WORD, 'doc'],
            'excel' => [MimeHelper::ICON_EXCEL, 'xls'],
            'powerpoint' => [MimeHelper::ICON_POWERPOINT, 'ppt'],
            'pdf' => [MimeHelper::ICON_PDF, 'pdf'],
            'image' => [MimeHelper::ICON_IMAGE, 'jpg'],
            'audio' => [MimeHelper::ICON_AUDIO, 'mp3'],
            'video' => [MimeHelper::ICON_VIDEO, 'avi'],
            'zip' => [MimeHelper::ICON_ZIP, 'zip'],
        ];
    }

    /**
     * Test unknown extension
     */
    public function testUnknownExtension()
    {
        $this->assertEquals(MimeHelper::ICON_FILE, MimeHelper::getMimeIconClassByExtension('unknown'));
    }

    /**
     * Test unknown extension
     */
    public function testUnknownExtensionFileObject()
    {
        $temp = new File;
        $temp->file_name = 'test.unknown';

        $this->assertEquals(MimeHelper::ICON_FILE, MimeHelper::getMimeIconClassByExtension($temp));
    }
}
