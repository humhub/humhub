<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\content\models\Content;
use humhub\modules\file\Events;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;
use yii\helpers\Console;

class FileAccessTest extends HumHubDbTestCase
{
    public function testUnassignedFileIsOnlyViewableByCreator()
    {
        $file = $this->createFile(['created_by' => 2]);

        $this->assertTrue($file->canView(User::findOne(2)));
        $this->assertFalse($file->canView(User::findOne(3)));
        $this->assertFalse($file->canView());
    }

    public function testPublicFileIsViewableByEveryone()
    {
        $file = $this->createFile(['created_by' => 2, 'public' => 1]);

        $this->assertTrue($file->canView(User::findOne(2)));
        $this->assertTrue($file->canView(User::findOne(3)));
        $this->assertTrue($file->canView());
    }

    public function testPublicFlagOverridesObjectPermissions()
    {
        self::becomeUser('Admin');

        $post = new Post(Space::findOne(5), Content::VISIBILITY_PRIVATE, ['message' => 'Private space post']);
        $this->assertTrue($post->save());

        $file = $this->createFile();
        $post->fileManager->attach($file);
        $file->refresh();

        self::logout();

        // User2 is no member of the private Space 5
        $this->assertFalse($file->canView(User::findOne(3)));

        $file->updateAttributes(['public' => 1]);
        $this->assertTrue($file->canView(User::findOne(3)));
    }

    public function testStandaloneFileCannotBeDeletedViaGenericApi()
    {
        $file = $this->createFile(['created_by' => 2, 'standalone' => 1, 'public' => 1]);

        $this->assertFalse($file->canDelete(User::findOne(2)));
        $this->assertFalse($file->canDelete(User::findOne(1)));
    }

    public function testUnassignedFileCanBeDeleted()
    {
        $file = $this->createFile(['created_by' => 2]);

        $this->assertTrue($file->canDelete(User::findOne(2)));
    }

    public function testCronCleanupKeepsStandaloneFiles()
    {
        $standalone = $this->createFile(['standalone' => 1]);
        $unassigned = $this->createFile();

        $outdated = date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 3);
        $standalone->updateAttributes(['created_at' => $outdated]);
        $unassigned->updateAttributes(['created_at' => $outdated]);

        Events::onCronDailyRun(new Event(['sender' => new class {
            public function stdout($string, ...$args)
            {
            }
        }]));

        $this->assertNotNull(File::findOne(['id' => $standalone->id]));
        $this->assertNull(File::findOne(['id' => $unassigned->id]));
    }

    private function createFile(array $attributes = []): File
    {
        $file = new File();
        $file->file_name = 'test.txt';
        $this->assertTrue($file->save());

        if ($attributes !== []) {
            $file->updateAttributes($attributes);
        }

        return $file;
    }
}
