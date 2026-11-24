<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\models\File;
use humhub\modules\file\models\FileHistory;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

class FileHistoryTest extends HumHubDbTestCase
{
    public function testRecordWithEnabledHistory()
    {
        $file = $this->prepareFile();
        $file->getPolymorphicRelation()->fileManagerEnableHistory = true;

        $file->setStoredFileContent('V1');
        $file->setStoredFileContent('V2');
        $file->setStoredFileContent('V3');

        // Test History creation
        $this->assertEquals(2, count($file->historyFiles));
        $this->assertSame('V3', $file->store->getContent());
    }

    public function testRollback()
    {
        $file = $this->prepareFile();
        $file->getPolymorphicRelation()->fileManagerEnableHistory = true;

        $file->setStoredFileContent('V1');
        $file->setStoredFileContent('V2');
        $file->setStoredFileContent('V3');

        /** @var FileHistory $previousVersion */
        $previousVersion = $file->getHistoryFiles()->one();

        // Test Rollback
        $file->setStoredFile($previousVersion->getFileStorePath());
        $this->assertSame('V2', $file->store->getContent());
    }


    public function testANewVersionFromAPathOnDiskIsAVersionLikeAnyOther()
    {
        $file = $this->prepareFile();
        $file->getPolymorphicRelation()->fileManagerEnableHistory = true;
        $file->setStoredFileContent('V1');

        $path = tempnam(sys_get_temp_dir(), 'history');
        file_put_contents($path, 'Version 2');
        $file->setStoredFileByPath($path);
        unlink($path);

        $this->assertSame('Version 2', $file->store->getContent());
        $this->assertSame(9, (int)$file->size);
        $this->assertSame(sha1('Version 2'), $file->hash_sha1);
        $this->assertEquals(1, count($file->historyFiles));
    }

    public function testRecordWithDisabledHistory()
    {
        $file = $this->prepareFile();

        $file->setStoredFileContent('V1');
        $file->setStoredFileContent('V2');
        $file->setStoredFileContent('V3');

        $this->assertEquals(0, count($file->historyFiles));
        $this->assertSame('V3', $file->store->getContent());
    }


    /**
     * @return File
     */
    private function prepareFile()
    {
        // A file can only be attached by its own creator, so we need an authenticated
        // user in context for both creating and attaching the file (see FileManager::attach()).
        self::becomeUser('Admin');

        $post = Post::findOne(['id' => 1]);

        $file = new File();
        $file->file_name = "Test";
        $file->save();

        $post->fileManager->attach($file);

        return $file;
    }

}
