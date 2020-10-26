<?php

namespace dashboard\unit;

use dashboard\DashboardStreamTest;
use humhub\modules\content\models\Content;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class DashboardMemberStreamQueryTest extends DashboardStreamTest
{

    /**
     * SPACE MEMBER TESTS
     */

    public function testSpaceMemberDoesSeePublicContentOnPublicSpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnPublicSpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceMemberDoesSeePublicContentOnUsersOnlySpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnUsersOnlySpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceMemberDoesSeePublicContentOnPrivateSpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnPrivateSpace()
    {
        $this->assertSpaceMemberCanSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PRIVATE);
    }

    private function assertSpaceMemberCanSee($spaceVisibility, $contentVisibility)
    {
        $user = User::findOne(['id' => 3]);
        $space = Space::findOne(['id' => 1]);
        $space->updateAttributes(['visibility' => $spaceVisibility]);

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    /**
     * SPACE NON MEMBER TESTS
     */

    public function testNonSpaceMemberDoesNotSeePublicContentOnPublicSpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testNonSpaceMemberDoesNotSeePrivateContentOnPublicSpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testNonSpaceMemberDoesSeePublicContentOnUsersOnlySpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testNonSpaceMemberDoesNotSeePrivateContentOnUsersOnlySpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    public function testNonSpaceMemberDoesNotSeePublicContentOnPrivateSpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PUBLIC);
    }

    public function testNonSpaceMemberDoesNotSeePrivateContentOnPrivateSpace()
    {
        $this->assertNonSpaceMemberDoesNotSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PRIVATE);
    }

    private function assertNonSpaceMemberDoesNotSee($spaceVisibility, $contentVisibility)
    {
        $user = User::findOne(['id' => 2]);
        $space = Space::findOne(['id' => 1]);
        $space->updateAttributes(['visibility' => $spaceVisibility]);

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(0, $stream);
    }

    /**
     * SPACE FOLLOWER TESTS
     */

    public function testSpaceFollowerDoesSeePublicContentOnPublicSpace()
    {
        $this->assertSpaceFollowerDoesSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceFollowerDoesNotSeePrivateContentOnPublicSpace()
    {
        $this->assertSpaceFollowerDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceFollowerDoesSeePublicContentOnUsersOnlySpace()
    {
        $this->assertSpaceFollowerDoesSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceFollowerDoesNotSeePrivateContentOnUsersOnlySpace()
    {
        $this->assertSpaceFollowerDoesNotSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceFollowerDoesNotSeePublicContentOnPrivateSpace()
    {
        $this->assertSpaceFollowerDoesSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceFollowerDoesNotSeePrivateContentOnPrivateSpace()
    {
        $this->assertSpaceFollowerDoesNotSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PRIVATE);
    }

    private function assertSpaceFollowerDoesSee($spaceVisibility, $contentVisibility)
    {
        $user = User::findOne(['id' => 2]);
        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->follow($user->id));
        $space->updateAttributes(['visibility' => $spaceVisibility]);

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertSpaceFollowerDoesNotSee($spaceVisibility, $contentVisibility)
    {
        $user = User::findOne(['id' => 2]);
        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->follow($user->id));
        $space->updateAttributes(['visibility' => $spaceVisibility]);

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(0, $stream);
    }

    /**
     * USER PROFILE TESTS
     */

    public function testUserDoesNotSeePublicContentOnPublicProfile()
    {
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnPublicProfile()
    {
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testUserDoesNotSeePublicContentOnMembersOnlyProfile()
    {
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnMembersOnlyProfile()
    {
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    /**
     * USER PROFILE TESTS WITH INCLUDE ALL PROFILES
     */

    public function testUserDoesSeePublicContentOnPublicProfileWithIncludeAll()
    {
        $this->enableAutoIncludeProfilePostsAll();
        $this->assertUserDoesSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnPublicProfileWithIncludeAll()
    {
        $this->enableAutoIncludeProfilePostsAll();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testUserDoesSeePublicContentOnMembersOnlyProfileWithIncludeAll()
    {
        $this->enableAutoIncludeProfilePostsAll();
        $this->assertUserDoesSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnMembersOnlyProfileWithIncludeAll()
    {
        $this->enableAutoIncludeProfilePostsAll();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }


    /**
     * USER PROFILE TESTS WITH INCLUDE ALL PROFILES ADMIN ONLY
     */

    public function testUserDoesNotSeePublicContentOnPublicProfileWithIncludeAllAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnPublicProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testUserDoesNotSeePublicContentOnMembersOnlyProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testUserDoesNotSeePrivateContentOnMembersOnlyProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    public function testAdminDoesSeePublicContentOnPublicProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, User::findOne(['id' => 1]));
    }

    public function testAdminDoesNotSeePrivateContentOnPublicProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE, User::findOne(['id' => 1]));
    }

    public function testAdminDoesSeePublicContentOnMembersOnlyProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC, User::findOne(['id' => 1]));
    }

    public function testAdminDoesNotSeePrivateContentOnMembersOnlyProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE, User::findOne(['id' => 1]));
    }


    private function assertUserDoesSeeProfileContent($userVisibility, $contentVisibility, $user = null)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = $user ?? User::findOne(['id' => 3]);

        $user1->updateAttributes(['visibility' => $userVisibility]);

        $content = $this->createContent($contentVisibility, $user1, $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertUserDoesNotSeeProfileContent($userVisibility, $contentVisibility,  $user = null)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = $user ?? User::findOne(['id' => 3]);

        $user1->updateAttributes(['visibility' => $userVisibility]);

        $content = $this->createContent($contentVisibility, $user1,  $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(0, $stream);
    }

    /**
     * USER PROFILE FOLLOW TESTS
     */

    public function testFollowingUserDoesSeePublicContentOnPublicProfile()
    {
        $this->assertFollowingUserDoesSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testFollowingUserDoesNotSeePrivateContentOnPublicProfile()
    {
        $this->assertFollowingUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testFollowingUserDoesSeePublicContentOnUsersOnlyProfile()
    {
        $this->assertFollowingUserDoesSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testFollowingUserDoesNotSeePrivateContentOnUsersOnlyProfile()
    {
        $this->assertFollowingUserDoesNotSeeProfileContent(User::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    private function assertFollowingUserDoesSeeProfileContent($userVisibility, $contentVisibility)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        // User2 follows user1
        static::assertTrue($user1->follow($user2));

        $user1->updateAttributes(['visibility' => $userVisibility]);

        $content = $this->createContent($contentVisibility, $user1, $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertFollowingUserDoesNotSeeProfileContent($userVisibility, $contentVisibility)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        // User2 follows user1
        static::assertTrue($user1->follow($user2));

        $user1->updateAttributes(['visibility' => $userVisibility]);

        $content = $this->createContent($contentVisibility, $user1, $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(0, $stream);
    }


    /**
     * USER PROFILE FRIENDSHIP TESTS
     */

    public function testFriendUserDoesSeePublicContent()
    {
        $this->assertFriendUserDoesSeeProfileContent(Content::VISIBILITY_PUBLIC);
    }

    public function testFriendUserDoesSeePrivateContent()
    {
        $this->assertFriendUserDoesSeeProfileContent(Content::VISIBILITY_PRIVATE);
    }

    private function assertFriendUserDoesSeeProfileContent($contentVisibility)
    {
        $this->enableFriendships();
        $user1 = User::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        static::assertTrue(Friendship::add($user1, $user2));
        static::assertTrue(Friendship::add($user2, $user1));

        $content = $this->createContent($contentVisibility, $user1, $user1->username);

        $stream = $this->fetchDashboardContent($user2);

        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    // TODO: TEST VISIBILITY AS AUTHOR, author should see his content in any case
}
