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
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnPublicSpace()
    {
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceMemberDoesSeePublicContentOnUsersOnlySpace()
    {
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnUsersOnlySpace()
    {
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_REGISTERED_ONLY, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceMemberDoesSeePublicContentOnPrivateSpace()
    {
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PUBLIC);
    }

    public function testSpaceMemberDoesSeePrivateContentOnPrivateSpace()
    {
        $this->assertSpaceMemberDoesSee(Space::VISIBILITY_NONE, Content::VISIBILITY_PRIVATE);
    }

    public function testSpaceMemberDoesNotSeeContentOnSpaceWithoutShowAtDashboard()
    {
        $this->assertSpaceMemberDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, 0);
    }

    public function testSpaceMemberDoesNotSeePublicContentOfArchivedSpace()
    {
        $this->assertSpaceMemberDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, 1 , Space::STATUS_ARCHIVED);
    }

    public function testSpaceMemberDoesNotSeePublicContentOfDisabledSpace()
    {
        $this->assertSpaceMemberDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, 1 , Space::STATUS_DISABLED);
    }

    private function assertSpaceMemberDoesSee($spaceVisibility, $contentVisibility, $showAtDashboard = 1, $state = Space::STATUS_ENABLED)
    {
        $user = User::findOne(['id' => 3]);
        $space = Space::findOne(['id' => 1]);
        $space->updateAttributes(['status' => $state, 'visibility' => $spaceVisibility]);

        $membership = $space->getMembership($user->id);
        $membership->updateAttributes(['show_at_dashboard' => $showAtDashboard]);

        static::assertTrue($space->isMember($user->id));

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertSpaceMemberDoesNotSee($spaceVisibility, $contentVisibility, $show_at_dahsboard = 1, $state = Space::STATUS_ENABLED)
    {
        $user = User::findOne(['id' => 3]);
        $space = Space::findOne(['id' => 1]);
        $space->updateAttributes(['status' => $state, 'visibility' => $spaceVisibility]);

        $membership = $space->getMembership($user->id);
        $membership->updateAttributes(['show_at_dashboard' => $show_at_dahsboard]);

        static::assertTrue($space->isMember($user->id));

        $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(0, $stream);
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

        $this->createContent($contentVisibility, $space);
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

    public function testSpaceFollowerDoesNotSeePublicContentOnArchivedSpace()
    {
        $this->assertSpaceFollowerDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, Space::STATUS_ARCHIVED);
    }

    public function testSpaceFollowerDoesNotSeePublicContentOnDisabledSpace()
    {
        $this->assertSpaceFollowerDoesNotSee(Space::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, Space::STATUS_ARCHIVED);
    }

    private function assertSpaceFollowerDoesSee($spaceVisibility, $contentVisibility, $state = Space::STATUS_ENABLED)
    {
        $user = User::findOne(['id' => 2]);
        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->follow($user->id));
        $space->updateAttributes(['visibility' => $spaceVisibility, 'status' => $state]);

        $content = $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertSpaceFollowerDoesNotSee($spaceVisibility, $contentVisibility, $state = Space::STATUS_ENABLED)
    {
        $user = User::findOne(['id' => 2]);
        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->follow($user->id));
        $space->updateAttributes(['visibility' => $spaceVisibility, 'status' => $state]);

        $this->createContent($contentVisibility, $space);
        $stream = $this->fetchDashboardContent($user);
        static::assertCount(0, $stream);
    }

    /**
     * USER PROFILE TESTS -> By default user do not see other users content unless he follows him/her
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

    public function testUserDoesNotSeeContentOfDisabledProfileWithIncludeAll()
    {
        $this->enableAutoIncludeProfilePostsAll();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, null,User::STATUS_DISABLED);
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

    public function testAdminDoesNotSeeContentOnDisabledProfileWithIncludeAdminOnly()
    {
        $this->enableAutoIncludeProfilePostsAdmin();
        $this->assertUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, User::findOne(['id' => 1]), User::STATUS_DISABLED);
    }

    private function assertUserDoesSeeProfileContent($userVisibility, $contentVisibility, $user = null , $status = User::STATUS_ENABLED)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = $user ?? User::findOne(['id' => 3]);

        $user1->updateAttributes(['visibility' => $userVisibility, 'status' => $status]);

        $content = $this->createContent($contentVisibility, $user1, $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertUserDoesNotSeeProfileContent($userVisibility, $contentVisibility,  $user = null, $status = User::STATUS_ENABLED)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = $user ?? User::findOne(['id' => 3]);

        $user1->updateAttributes(['visibility' => $userVisibility, 'status' => $status]);

        $this->createContent($contentVisibility, $user1,  $user1->username);
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

    public function testFollowingUserDoesNotSeeContentOfDisabledProfile()
    {
        $this->assertFollowingUserDoesNotSeeProfileContent(User::VISIBILITY_ALL, Content::VISIBILITY_PUBLIC, User::STATUS_DISABLED);
    }

    private function assertFollowingUserDoesSeeProfileContent($userVisibility, $contentVisibility, $status = User::STATUS_ENABLED)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        // User2 follows user1
        static::assertTrue($user1->follow($user2));

        $user1->updateAttributes(['visibility' => $userVisibility, 'status' => $status]);

        $content = $this->createContent($contentVisibility, $user1, $user1->username);
        $stream = $this->fetchDashboardContent($user2);
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertFollowingUserDoesNotSeeProfileContent($userVisibility, $contentVisibility, $status = User::STATUS_ENABLED)
    {
        $user1 = User::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        // User2 follows user1
        static::assertTrue($user1->follow($user2));

        $user1->updateAttributes(['visibility' => $userVisibility, 'status' => $status]);

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

    public function testFriendUserDoesNotSeeContentOfDisabledProfile()
    {
        $this->assertFriendUserDoesNotSeeProfileContent(Content::VISIBILITY_PUBLIC, false, User::STATUS_DISABLED);
    }

    public function testFriendRequestedUserDoesSeePublicContent()
    {
        $this->assertFriendUserDoesSeeProfileContent(Content::VISIBILITY_PUBLIC, true);
    }

    public function testFriendRequestedUserDoesNotSeePrivateContent()
    {
        $this->assertFriendUserDoesNotSeeProfileContent(Content::VISIBILITY_PRIVATE, true);
    }

    private function assertFriendUserDoesSeeProfileContent($contentVisibility, $requested = false, $status = User::STATUS_ENABLED)
    {
        $this->enableFriendships();

        $user1 = User::findOne(['id' => 2]);

        $user1->updateAttributes(['status' => $status]);

        $user2 = User::findOne(['id' => 3]);

        static::assertTrue(Friendship::add($user2, $user1));

        if(!$requested) {
            static::assertTrue(Friendship::add($user1, $user2));
        }

        $content = $this->createContent($contentVisibility, $user1, $user1->username);

        $stream = $this->fetchDashboardContent($user2);

        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    private function assertFriendUserDoesNotSeeProfileContent($contentVisibility, $requested = false, $status = User::STATUS_ENABLED)
    {
        $this->enableFriendships();

        $user1 = User::findOne(['id' => 2]);

        $user1->updateAttributes(['status' => $status]);

        $user2 = User::findOne(['id' => 3]);

        static::assertTrue(Friendship::add($user2, $user1));

        if(!$requested) {
            static::assertTrue(Friendship::add($user1, $user2));
        }

        $this->createContent($contentVisibility, $user1, $user1->username);

        $stream = $this->fetchDashboardContent($user2);

        static::assertCount(0, $stream);
    }

    // TODO: TEST VISIBILITY AS AUTHOR, author should see his content in any case
}
