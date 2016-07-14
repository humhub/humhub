<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearchTest\Lucene\Storage;

use ZendSearch\Lucene\Storage\Directory;
use ZendSearch\Lucene\Storage\File;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFilesystem()
    {
        $tempPath = __DIR__ . '/_tempFiles/_files';

        // remove files from temporary directory
        $dir = opendir($tempPath);
        while (($file = readdir($dir)) !== false) {
            if (!is_dir($tempPath . '/' . $file)) {
                @unlink($tempPath . '/' . $file);
            }
        }
        closedir($dir);

        $directory = new Directory\Filesystem($tempPath);

        $this->assertTrue($directory instanceof Directory\DirectoryInterface);
        $this->assertEquals(count($directory->fileList()), 0);

        $fileObject = $directory->createFile('file1');
        $this->assertTrue($fileObject instanceof File\FileInterface);
        unset($fileObject);
        $this->assertEquals($directory->fileLength('file1'), 0);

        $this->assertEquals(count(array_diff($directory->fileList(), array('file1'))), 0);

        $directory->deleteFile('file1');
        $this->assertEquals(count($directory->fileList()), 0);

        $this->assertFalse($directory->fileExists('file2'));

        $fileObject = $directory->createFile('file2');
        $this->assertEquals($directory->fileLength('file2'), 0);
        $fileObject->writeBytes('0123456789');
        unset($fileObject);
        $this->assertEquals($directory->fileLength('file2'), 10);

        $directory->renameFile('file2', 'file3');
        $this->assertEquals(count(array_diff($directory->fileList(), array('file3'))), 0);

        $modifiedAt1 = $directory->fileModified('file3');
        clearstatcache();
        $directory->touchFile('file3');
        $modifiedAt2 = $directory->fileModified('file3');
        sleep(1);
        clearstatcache();
        $directory->touchFile('file3');
        $modifiedAt3 = $directory->fileModified('file3');

        $this->assertTrue($modifiedAt2 >= $modifiedAt1);
        $this->assertTrue($modifiedAt3 >  $modifiedAt2);

        $fileObject = $directory->getFileObject('file3');
        $this->assertEquals($fileObject->readBytes($directory->fileLength('file3')), '0123456789');
        unset($fileObject);

        $fileObject = $directory->createFile('file3');
        $this->assertEquals($fileObject->readBytes($directory->fileLength('file3')), '');
        unset($fileObject);

        $directory->deleteFile('file3');
        $this->assertEquals(count($directory->fileList()), 0);

        $directory->close();
    }

    public function testFilesystemSubfoldersAutoCreation()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_tempFiles/_files/dir1/dir2/dir3');
        $this->assertTrue($directory instanceof Directory\DirectoryInterface);
        $directory->close();

        rmdir(__DIR__ . '/_tempFiles/_files/dir1/dir2/dir3');
        rmdir(__DIR__ . '/_tempFiles/_files/dir1/dir2');
        rmdir(__DIR__ . '/_tempFiles/_files/dir1');
    }
}

