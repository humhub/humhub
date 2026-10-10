<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\jobs\AddUsersToSpaceJob;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\SpaceInviteNotification;
use humhub\modules\space\notifications\UserAddedNotification;
use tests\codeception\_support\HumHubDbTestCase;

class AddUsersToSpaceJobTest extends HumHubDbTestCase
{
    public function testForcedMembershipNotifiesEachAddedUser()
    {
        $this->becomeUser('Admin');

        // users 2 and 4 are no members of space 1
        (new AddUsersToSpaceJob([
            'spaceId' => 1,
            'userIds' => [2, 4],
            'originatorId' => 1,
            'forceMembership' => true,
        ]))->run();

        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->isMember(2));
        $this->assertTrue($space->isMember(4));
        $this->assertHasNotification(UserAddedNotification::class, $space, 1, 2);
        $this->assertHasNotification(UserAddedNotification::class, $space, 1, 4);
        $this->assertEqualsNotificationCount(2, UserAddedNotification::class, $space);
        $this->assertHasNoNotification(SpaceInviteNotification::class, $space);
    }
}
