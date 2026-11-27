<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\file\Events;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Membership;
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

    public function testUnassignedFileCannotBeDeletedByNonCreator()
    {
        $file = $this->createFile(['created_by' => 2]);

        $this->assertFalse($file->canDelete(User::findOne(3)));
        $this->assertFalse($file->canDelete());
    }

    public function testUnassignedFileCannotBeAttachedByNonCreator()
    {
        self::becomeUser('Admin');
        $post = new Post(Space::findOne(5), Content::VISIBILITY_PRIVATE, ['message' => 'Attach IDOR test']);
        $this->assertTrue($post->save());
        self::logout();

        // File was created/uploaded by User 2, but a different user (User 3) tries to attach it
        self::becomeUser(User::findOne(3)->username);
        $file = $this->createFile(['created_by' => 2]);
        $post->fileManager->attach($file);
        self::logout();

        $file->refresh();
        $this->assertTrue(empty($file->object_model));
        $this->assertFalse($file->isAssigned());
    }

    public function testUnassignedFileCanBeAttachedByCreator()
    {
        self::becomeUser('Admin');
        $post = new Post(Space::findOne(5), Content::VISIBILITY_PRIVATE, ['message' => 'Attach by creator test']);
        $this->assertTrue($post->save());

        $file = $this->createFile();
        $post->fileManager->attach($file);
        self::logout();

        $file->refresh();
        $this->assertTrue($file->isAssigned());
        $this->assertEquals($post->id, $file->object_id);
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

    public function testPrivateSpaceFileIsOnlyViewableWithSpaceAccess()
    {
        self::allowGuestAccess();
        $space = Space::findOne(5);
        $file = $this->createContainerFile($space);

        // User1 is a member, User2 is not
        $this->assertTrue($file->canView(User::findOne(2)));
        $this->assertFalse($file->canView(User::findOne(3)));
        $this->assertFalse($file->canView(3));
        $this->assertFalse($file->canView());

        self::becomeUser('User2');
        $this->assertFalse($file->canView());
        self::logout();

        // Invited users can open the pages of a private Space
        $this->assertNotFalse($space->inviteMember(3, 1, false));
        $this->assertTrue($file->canView(User::findOne(3)));
    }

    public function testPrivateSpaceFileIsNotViewableByFormerMember()
    {
        $space = Space::findOne(5);
        $file = $this->createContainerFile($space);

        self::becomeUser('Admin');
        $this->assertTrue($space->removeMember(2));
        self::logout();

        $this->assertFalse($file->canView(User::findOne(2)));
    }

    public function testPrivateSpaceFileIsViewableBySystemAdmin()
    {
        $space = Space::findOne(5);
        $file = $this->createContainerFile($space);
        Membership::findOne(['space_id' => 5, 'user_id' => 1])->delete();

        $this->assertTrue($file->canView(User::findOne(1)));
    }

    public function testRegisteredOnlySpaceFileIsNotViewableByGuests()
    {
        self::allowGuestAccess();
        $file = $this->createContainerFile(Space::findOne(1));

        // User3 (ID 4) is no member of Space 1
        $this->assertTrue($file->canView(User::findOne(4)));
        $this->assertFalse($file->canView());
    }

    public function testPublicSpaceFileFollowsGuestAccessSetting()
    {
        $file = $this->createContainerFile(Space::findOne(2));

        $this->assertTrue($file->canView(User::findOne(3)));
        $this->assertFalse($file->canView());

        self::allowGuestAccess();
        $this->assertTrue($file->canView());
    }

    public function testSpaceFileIsNotViewableByBlockedUser()
    {
        $space = Space::findOne(2);
        $file = $this->createContainerFile($space);
        ContentContainerBlockedUsers::updateByContainer($space, [User::findOne(3)->guid]);

        $this->assertFalse($file->canView(User::findOne(3)));
    }

    public function testUserFileFollowsProfileVisibility()
    {
        self::allowGuestAccess();
        $user = User::findOne(2);
        $file = $this->createContainerFile($user);

        $this->assertTrue($file->canView(User::findOne(3)));
        $this->assertFalse($file->canView());

        $user->updateAttributes(['visibility' => User::VISIBILITY_ALL]);
        // Reload to drop the cached parent record
        $this->assertTrue(File::findOne($file->id)->canView());
    }

    private function createContainerFile(ContentContainerActiveRecord $container): File
    {
        $file = $this->createFile();
        $container->fileManager->attach($file, true);
        $file->refresh();
        $this->assertTrue($file->isAssignedTo($container));

        return $file;
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
