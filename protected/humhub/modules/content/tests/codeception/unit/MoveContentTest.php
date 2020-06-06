<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 24.07.2017
 * Time: 15:56
 */

namespace humhub\modules\content\tests\codeception\unit;


use humhub\libs\BasePermission;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\models\Content;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\post\models\Post;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

class MoveContentTest extends HumHubDbTestCase
{
    public function testCanMoveOwnProfileContent()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);

        $this->becomeUser('User1');

        $profilePost = Content::findOne(['id' => 3]);
        $this->assertEquals(2, $profilePost->created_by);

        // User is allowed to move this content
        $this->assertTrue($profilePost->canMove() === true);

        // User is allowed to move this content to space2 as space admin
        $this->assertTrue($profilePost->canMove($space2) === true);

        // User is not allowed to move this content to space1
        $this->assertTrue($profilePost->canMove($space1) !== true);

        // User is allowed to move this content to space3 as member
        $this->assertTrue($profilePost->canMove($space3) === true);
    }

    public function testCanMoveOtherProfileContentAsAdmin()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);

        $this->becomeUser('Admin');

        $profilePost = Content::findOne(['id' => 3]);
        $this->assertEquals(2, $profilePost->created_by);

        // Admin is allowed to move this content
        $this->assertTrue($profilePost->canMove() === true);

        // Admin is allowed to move this content to space2 as space admin
        $this->assertTrue($profilePost->canMove($space2) === true);

        // Admin is not allowed to move this content to space1 since author can't create private content
        $this->assertTrue($profilePost->canMove($space1) !== true);

        // Admin is allowed to move this content to space3 as member
        $this->assertTrue($profilePost->canMove($space3) === true);
    }

    public function testCanMoveOtherProfileContentAsNonAdmin()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);

        $this->becomeUser('User3');

        $profilePost = Content::findOne(['id' => 3]);
        $this->assertEquals(2, $profilePost->created_by);

        // Admin is allowed to move this content
        $this->assertTrue($profilePost->canMove()  !== true);

        // Admin is allowed to move this content to space2 as space admin
        $this->assertTrue($profilePost->canMove($space2)  !== true);

        // Admin is not allowed to move this content to space1 since author can't create private content
        $this->assertTrue($profilePost->canMove($space1) !== true);

        // Admin is allowed to move this content to space3 as member
        $this->assertTrue($profilePost->canMove($space3)  !== true);
    }

    public function testCanMoveSpaceContentAsSpaceAdmin()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);
        $space4 = Space::findOne(4);

        $this->becomeUser('User1');

        $post = Content::findOne(['id' => 12]);

        // Space Admin is allowed to move this content
        $this->assertTrue($post->canMove() === true);

        // Can not be moved to current space
        $this->assertTrue($post->canMove($space2)  !== true);

        // Space Admin is not allowed to move this content to space1 since User1 is not member os space1
        $this->assertTrue($post->canMove($space1) !== true);

        // Space Admin is not allowed to move this content to space3 since User1 is only member os space3
        $this->assertTrue($post->canMove($space3)  !== true);

        // Space Admin is allowed to move this content to space3 since User1 admin on space4
        $this->assertTrue($post->canMove($space4) === true);
    }

    public function testCanMoveSpacePostAsUserManager()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);
        $space4 = Space::findOne(4);

       $this->setGroupPermission(3, ManageUsers::class);

        $this->becomeUser('User2');

        $post = Content::findOne(['id' => 12]);

        // User Manager is allowed to move this content
        $this->assertTrue($post->canMove() === true);

        // Can not be moved to current space
        $this->assertTrue($post->canMove($space2)  !== true);

        // User Manager is not allowed to move this content to space1 since User1 is not member os space1
        $this->assertTrue($post->canMove($space1) === true);

        // User Manager is allowed to move this content to space3 since
        $this->assertTrue($post->canMove($space3) === true);

        // Space Admin is allowed to move this content to space3  Space Admin User1 admin on space4
        $this->assertTrue($post->canMove($space4) === true);
    }

    public function testCanMoveProfileContentAsUserManager()
    {
        $space1 = Space::findOne(1);
        $space2 = Space::findOne(2);
        $space3 = Space::findOne(3);

        $this->setGroupPermission(3, ManageUsers::class);

        // Login as non author with manage user permission
        $this->becomeUser('User2');

        $profilePost = Content::findOne(['id' => 3]);
        $this->assertEquals(2, $profilePost->created_by);

        // User is allowed to move this content
        $this->assertTrue($profilePost->canMove() === true);

        // User is allowed to move this content to space2 as space admin
        $this->assertTrue($profilePost->canMove($space2) === true);

        // User is not allowed to move this content to space1
        $this->assertTrue($profilePost->canMove($space1) !== true);

        // User is allowed to move this content to space3 as member
        $this->assertTrue($profilePost->canMove($space3) === true);
    }

    public function testMoveProfileContent()
    {
        $space2 = Space::findOne(2);

        $this->setGroupPermission(3, ManageUsers::class);

        $profilePost = Content::findOne(['id' => 3]);

        // Login as non author with manage user permission
        $this->becomeUser('User2');

        // User2 is usermanager and has permission to move content
        $this->assertTrue($profilePost->move($space2) === true);

        $spaceContent = Content::findOne(['id' => 3]);
        $this->assertTrue($spaceContent->container instanceof Space);
        $this->assertEquals($spaceContent->container->id, $space2->id);
    }

    public function testMoveSpaceContent()
    {
        $space3 = Space::findOne(3);

        $this->setGroupPermission(3, ManageUsers::class);

        $post = Content::findOne(['id' => 11]);

        // Login as non author with manage user permission
        $this->becomeUser('User2');

        // User2 is usermanager and has permission to move content
        $this->assertEquals(true, $post->move($space3) );

        $spaceContent = Content::findOne(['id' => 11]);
        $this->assertTrue($spaceContent->container instanceof Space);
        $this->assertEquals($spaceContent->container->id, $space3->id);
    }

    public function testForceMoveContent()
    {
        $space3 = Space::findOne(3);

        $post = Content::findOne(['id' => 11]);

        // Login as non author with manage user permission
        $this->becomeUser('User1');

        // User2 is usermanager and has permission to move content
        $this->assertEquals(true, $post->move($space3, true) );

        $spaceContent = Content::findOne(['id' => 11]);
        $this->assertTrue($spaceContent->container instanceof Space);
        $this->assertEquals($spaceContent->container->id, $space3->id);
    }

    public function testCanMovePublicContentDeny()
    {
        $this->becomeUser('User1');

        // Disable public content creation on space3
        $space3 = Space::findOne(3);
        $space3->permissionManager->setGroupState(Space::USERGROUP_MEMBER, CreatePublicContent::class, BasePermission::STATE_DENY);

        // Create public post on space4
        $space4 = Space::findOne(4);
        $post = new Post($space4, Content::VISIBILITY_PUBLIC, ['message' => 'Test']);
        $this->assertTrue($post->save());

        $this->assertNotTrue($post->move($space3));
    }

    public function testCanMovePublicContentAllow()
    {
        $this->becomeUser('User1');

        // Disable public content creation on space3
        $space3 = Space::findOne(3);
        $space3->permissionManager->setGroupState(Space::USERGROUP_MEMBER, CreatePublicContent::class, BasePermission::STATE_ALLOW);

        // Create public post on space4
        $space4 = Space::findOne(4);
        $post = new Post($space4, Content::VISIBILITY_PUBLIC, ['message' => 'Test']);
        $this->assertTrue($post->save());

        $this->assertTrue($post->move($space3));
    }

    public function testCanMoveContentPostPermissionAllow()
    {
        $this->becomeUser('User1');

        // Disable public content creation on space3
        $space3 = Space::findOne(3);
        $space3->permissionManager->setGroupState(Space::USERGROUP_MEMBER, CreatePost::class, BasePermission::STATE_ALLOW);

        // Create public post on space4
        $space4 = Space::findOne(4);
        $post = new Post($space4, Content::VISIBILITY_PRIVATE, ['message' => 'Test']);
        $this->assertTrue($post->save());

        $this->assertTrue($post->move($space3));
    }

    public function testCanMoveContentPostPermissionDeny()
    {
        $this->becomeUser('User1');

        // Disable public content creation on space3
        $space3 = Space::findOne(3);
        $space3->permissionManager->setGroupState(Space::USERGROUP_MEMBER, CreatePost::class, BasePermission::STATE_DENY);

        // Create public post on space4
        $space4 = Space::findOne(4);
        $post = new Post($space4, Content::VISIBILITY_PRIVATE, ['message' => 'Test']);
        $this->assertTrue($post->save());

        $this->assertNotTrue($post->move($space3));
    }
}
