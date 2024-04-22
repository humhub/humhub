<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use Exception;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use SplFileInfo;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class FileHelperTest extends HumHubDbTestCase
{
    protected $fixtureConfig = false;

    protected function setUp(): void
    {
        parent::setUp();

        Yii::setAlias('@filestore', Yii::getAlias('@tests/codeception/_data'));
    }

    public function testHasExtension()
    {
        static::assertFalse(FileHelper::hasExtension(""));
        static::assertFalse(FileHelper::hasExtension("someDocument"));
        static::assertFalse(FileHelper::hasExtension("path/someDocument"));

        static::assertTrue(FileHelper::hasExtension("someDocument.z"));
        static::assertTrue(FileHelper::hasExtension("someDocument.doc"));
    }

    public function testHasExtensionFalsePositives()
    {
        // ToDo: False Positives

        $file = ".htaccess";
        static::assertTrue(FileHelper::hasExtension($file));
        static::assertEquals('htaccess', pathinfo($file, PATHINFO_EXTENSION));
        static::assertEquals('', pathinfo($file, PATHINFO_FILENAME));
        // possible solution
        static::assertFalse(self::hasExtension1($file));
        static::assertFalse(self::hasExtension2($file));

        $file = "doc.";
        static::assertTrue(FileHelper::hasExtension($file));
        // possible solution
        static::assertFalse(self::hasExtension1($file));
        static::assertFalse(self::hasExtension2($file));

        $file = "som/doc.with/extension";
        static::assertTrue(FileHelper::hasExtension($file));
        // possible solution
        static::assertFalse(self::hasExtension1($file));
        static::assertFalse(self::hasExtension2($file));

        static::assertFalse(self::hasExtension1(""));
        static::assertFalse(self::hasExtension2(""));
        static::assertFalse(self::hasExtension1("someDocument"));
        static::assertFalse(self::hasExtension2("someDocument"));
        static::assertFalse(self::hasExtension1("path/someDocument"));
        static::assertFalse(self::hasExtension2("path/someDocument"));

        static::assertTrue(self::hasExtension1("someDocument.z"));
        static::assertTrue(self::hasExtension2("someDocument.z"));
        static::assertTrue(self::hasExtension1("someDocument.doc"));
        static::assertTrue(self::hasExtension2("someDocument.doc"));
    }

    public function testGetExtension()
    {
        static::assertEquals('', FileHelper::getExtension(null));
        static::assertEquals('', FileHelper::getExtension(null));
    }

    public function testGetExtensionNotSupported()
    {
        static::assertEquals('', FileHelper::getExtension(new SplFileInfo(__FILE__)));
    }

    public function testCreateLinkException()
    {
        $path = 'test_image.jpg';

        $file = new File();

        $file->file_name = $path;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File GUID empty!');

        static::assertNull(FileHelper::createLink($file));
    }

    public function testCreateLink()
    {
        $label = 'my label';
        $path = 'test_image.jpg';
        $target = '_top';
        $variant = 'test';

        $file = new File();

        $file->file_name = $path;
        $file->guid = $guid = 'e882f005-efd5-4b8f-aadb-2726621b960f'; // file does not exist
        $hash = '';

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $path . '</a>',
            FileHelper::createLink($file),
        );

        $file->guid = $guid = 'e882f005-efd5-4b8f-aadb-2726621b960a'; // file does exist
        $hash = '638184a3';

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $path . '</a>',
            FileHelper::createLink($file),
        );

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" label="' . $label . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $label . '</a>',
            FileHelper::createLink($file, null, ['label' => $label]),
        );

        // target gets ignored for downloads
        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $path . '</a>',
            FileHelper::createLink($file, null, ['target' => $target]),
        );

        $file->mime_type = $mimeType = 'image/jpeg';

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>',
            FileHelper::createLink($file),
        );

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>',
            FileHelper::createLink($file, ['variant' => $variant]),
        );

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>',
            FileHelper::createLink($file, ['download' => true]),
        );

        static::assertEquals(
            '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>',
            FileHelper::createLink($file, ['variant' => $variant, 'download' => true]),
        );
    }

    public function testGetFileInfosException()
    {
        $path = 'test_image.jpg';

        $file = new File();

        $file->file_name = $path;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File GUID empty!');

        static::assertNull(FileHelper::getFileInfos($file));
    }

    public function testGetFileInfos()
    {
        $file = new File();

        $file->file_name = $path = 'test_image.jpg';
        $file->guid = $guid = 'e882f005-efd5-4b8f-aadb-2726621b960f';
        $hash = '';

        $expected = [
            'name' => $path,
            'guid' => $guid,
            'size' => null,
            'mimeType' => null,
            'mimeIcon' => 'mime-image',
            'size_format' => '<span class="not-set">(not set)</span>',
            'url' => 'http://localhost/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'relUrl' => '/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'openLink' => '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $path . '</a>',
            'thumbnailUrl' => '',
        ];

        static::assertEquals($expected, FileHelper::getFileInfos($file));

        $file->file_name = $path = Yii::getAlias('default_user.jpg');
        $file->guid = $guid = 'e882f005-efd5-4b8f-aadb-2726621b960a';
        $hash = '638184a3';

        $expected = [
            'name' => $path,
            'guid' => $guid,
            'size' => null,
            'mimeType' => null,
            'mimeIcon' => 'mime-image',
            'size_format' => '<span class="not-set">(not set)</span>',
            'url' => 'http://localhost/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'relUrl' => '/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'openLink' => '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '">' . $path . '</a>',
            'thumbnailUrl' => '',
        ];

        static::assertEquals($expected, FileHelper::getFileInfos($file));

        $file->mime_type = $mimeType = 'image/jpeg';

        $expected = [
            'name' => $path,
            'guid' => $guid,
            'size' => null,
            'mimeType' => $mimeType,
            'mimeIcon' => 'mime-image',
            'size_format' => '<span class="not-set">(not set)</span>',
            'url' => 'http://localhost/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'relUrl' => '/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash,
            'openLink' => '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>',
            'thumbnailUrl' => 'http://localhost/index-test.php?r=file%2Ffile%2Fdownload&variant=preview-image&guid=' . $guid . '&hash_sha1=' . $hash,
        ];

        static::assertEquals($expected, FileHelper::getFileInfos($file));

        $variant = 'test';

        $expected['url'] = 'http://localhost/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash;
        $expected['relUrl'] = '/index-test.php?r=file%2Ffile%2Fdownload&guid=' . $guid . '&hash_sha1=' . $hash;
        $expected['openLink'] = '<a href="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" target="_blank" data-pjax-prevent data-file-download data-file-url="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;download=1&amp;guid=' . $guid . '&amp;hash_sha1=' . $hash . '" data-file-name="' . $path . '" data-file-mime="' . $mimeType . '">' . $path . '</a>';

        static::assertEquals($expected, FileHelper::getFileInfos($file, $variant));
    }

    public static function hasExtension1(string $file): bool
    {
        return pathinfo($file, PATHINFO_FILENAME) && pathinfo($file, PATHINFO_EXTENSION);
    }

    public static function hasExtension2(string $file): bool
    {
        $path_parts = pathinfo($file);
        return ($path_parts['filename'] ?? false) && ($path_parts['extension'] ?? false);
    }
}
