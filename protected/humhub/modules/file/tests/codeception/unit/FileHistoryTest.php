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
        $this->assertSame('V3', file_get_contents($file->store->get()));
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
        $this->assertSame('V2', file_get_contents($file->store->get()));
    }


    public function testRecordWithDisabledHistory()
    {
        $file = $this->prepareFile();

        $file->setStoredFileContent('V1');
        $file->setStoredFileContent('V2');
        $file->setStoredFileContent('V3');

        $this->assertEquals(0, count($file->historyFiles));
        $this->assertSame('V3', file_get_contents($file->store->get()));
    }


    /**
     * @return File
     */
    private function prepareFile()
    {
        $post = Post::findOne(['id' => 1]);

        $file = new File();
        $file->file_name = "Test";
        $file->save();

        $post->fileManager->attach($file);

        return $file;
    }

}
