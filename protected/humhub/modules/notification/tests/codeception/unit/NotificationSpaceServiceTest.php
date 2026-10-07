<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\services\NotificationSpaceService;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The follower scenarios of `content\tests\...\ContentCreatedTest` (which count the mails of the
 * "new content" notification) asserted on the follower query itself.
 *
 * Fixture: space 1 has the members 1 and 3 without `send_notifications`; space 3 has user 2 as
 * member with `send_notifications`; user 1 follows space 2 with `send_notifications`; the enabled
 * users are 1, 2, 3, 4 and 8.
 */
class NotificationSpaceServiceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    private const SPACE1_GUID = '5396d499-20d6-4233-800b-c6c86e5fa34a';

    private NotificationSpaceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // the follower and space queries are filtered by the visibility for the current user
        $this->becomeUser('Admin');
        $this->service = new NotificationSpaceService();
    }

    public function testMemberWithSendNotificationsFollowsPrivateContent()
    {
        $this->assertSame([2], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 3]), false)));
        $this->assertSame([], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), false)));
    }

    public function testFollowerWithSendNotificationsFollowsPublicContentOnly()
    {
        $space = Space::findOne(['id' => 2]);
        $this->assertContains(1, $this->ids($this->service->getContainerFollowers($space, true)));
        $this->assertNotContains(1, $this->ids($this->service->getContainerFollowers($space, false)));
    }

    public function testFollowersOfContentDependOnItsVisibility()
    {
        Membership::updateAll(['send_notifications' => 1], ['space_id' => 1, 'user_id' => 3]);
        Space::findOne(['id' => 1])->follow(User::findOne(['id' => 4]), true);

        // post 7 is public, post 8 private, both in space 1
        $this->assertSame([3, 4], $this->ids($this->service->getFollowers(Post::findOne(['id' => 7])->content)));
        $this->assertSame([3], $this->ids($this->service->getFollowers(Post::findOne(['id' => 8])->content)));
    }

    public function testUserContainerFollowers()
    {
        // user 1 follows user 2: the user and, for public content, their followers
        $this->assertSame([1, 2], $this->ids($this->service->getContainerFollowers(User::findOne(['id' => 2]), true)));
        $this->assertSame([2], $this->ids($this->service->getContainerFollowers(User::findOne(['id' => 2]), false)));
    }

    /**
     * ContentCreatedTest::testDefaultSpaceFollowPrivatePostNotification
     */
    public function testDefaultSpaceAppliesToMembersOfPrivateContent()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);
        $this->assertSame([1, 3], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), false)));
    }

    /**
     * ContentCreatedTest::testExplicitlyDisableDefaultNotificationSpace
     */
    public function testTouchedSettingsOptOutOfTheDefaultSpaceForPrivateContent()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);
        $this->touch(1);

        $this->assertSame([3], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), false)));
    }

    /**
     * ContentCreatedTest::testDefaultSpaceFollowPublicPostNotification
     */
    public function testDefaultSpaceAppliesToAllUsersForPublicContent()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);
        $this->assertSame([1, 2, 3, 4, 8], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), true)));
    }

    /**
     * ContentCreatedTest::testExplicitlyDisableDefaultNotificationPublicMember, ...Public and ...Public2
     */
    public function testTouchedSettingsOptOutOfTheDefaultSpaceForPublicContent()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);

        $this->touch(1);
        $this->assertSame([2, 3, 4, 8], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), true)));

        $this->touch(2);
        $this->assertSame([3, 4, 8], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 1]), true)));
    }

    public function testGetSpacesIncludesTheDefaultSpacesUntilTouched()
    {
        $user = User::findOne(['id' => 2]);
        $this->assertSame([3], $this->spaceIds($this->service->getSpaces($user)));
        $this->assertFalse($this->service->hasSpace(Space::findOne(['id' => 1]), $user));
        $this->assertSame([], $this->spaceIds($this->service->getDefaultNotificationSpaces()));

        $this->service->setSpaces([self::SPACE1_GUID]);
        $this->assertSame([1], $this->spaceIds($this->service->getDefaultNotificationSpaces()));
        $this->assertSame([1, 3], $this->spaceIds($this->service->getSpaces($user)));
        $this->assertTrue($this->service->hasSpace(Space::findOne(['id' => 1]), $user));

        $this->touch(2);
        $this->assertSame([3], $this->spaceIds($this->service->getSpaces($user)));
    }

    public function testSetSpacesMarksTheSettingsTouchedAndUnsetsTheOthers()
    {
        $user = User::findOne(['id' => 2]);
        $this->assertFalse(NotificationSpaceService::isTouchedSettings($user));
        $this->assertTrue($this->service->isFollowingSpace($user, Space::findOne(['id' => 3])));

        $this->service->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34d'], $user);
        // memberships are cached per request
        Yii::$app->runtimeCache->flush();

        $this->assertTrue(NotificationSpaceService::isTouchedSettings($user));
        $this->assertTrue($this->service->isFollowingSpace($user, Space::findOne(['id' => 4])));
        $this->assertFalse($this->service->isFollowingSpace($user, Space::findOne(['id' => 3])));
        $this->assertSame([4], $this->spaceIds($this->service->getSpaces($user)));
    }

    public function testSetSpaceSettingAppliesTheDefaultSpacesOnFirstTouch()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);
        $user = User::findOne(['id' => 3]);

        $this->service->setSpaceSetting($user, Space::findOne(['id' => 3]));

        $this->assertTrue(NotificationSpaceService::isTouchedSettings($user));
        // the default space 1 is kept as an explicit setting
        $this->assertSame([1, 3], $this->spaceIds($this->service->getSpaces($user)));

        $this->service->setSpaceSetting($user, Space::findOne(['id' => 1]), false);
        $this->assertSame([3], $this->spaceIds($this->service->getSpaces($user)));
    }

    public function testResetSpaces()
    {
        $this->service->resetSpaces();
        $this->assertSame([], $this->spaceIds($this->service->getSpaces(User::findOne(['id' => 2]))));
        $this->assertSame([], $this->ids($this->service->getContainerFollowers(Space::findOne(['id' => 2]), true)));
    }

    public function testNonNotificationSpaces()
    {
        $this->assertSame([2, 4, 5], $this->spaceIds($this->service->getNonNotificationSpaces(User::findOne(['id' => 2]))));

        $this->service->setSpaces([self::SPACE1_GUID]);
        $this->assertNotContains(1, $this->spaceIds($this->service->getNonNotificationSpaces()));
    }

    public function testDeprecatedManagerForwardsToTheService()
    {
        $this->service->setSpaces([self::SPACE1_GUID]);
        $space = Space::findOne(['id' => 1]);

        $this->assertSame(
            $this->ids($this->service->getContainerFollowers($space, true)),
            $this->ids(Yii::$app->notification->getContainerFollowers($space)),
        );
        $this->assertSame(
            $this->ids($this->service->getFollowers(Post::findOne(['id' => 8])->content)),
            $this->ids(Yii::$app->notification->getFollowers(Post::findOne(['id' => 8])->content)),
        );

        $user = User::findOne(['id' => 2]);
        Yii::$app->notification->setSpaces(['5396d499-20d6-4233-800b-c6c86e5fa34d'], $user);
        $this->assertSame([4], $this->spaceIds(Yii::$app->notification->getSpaces($user)));
        $this->assertTrue(\humhub\modules\notification\components\NotificationManager::isTouchedSettings($user));
    }

    /**
     * Marks the user's notification settings as touched, as saving the settings form does.
     */
    private function touch(int $userId): void
    {
        Yii::$app->getModule('notification')->settings->user(User::findOne(['id' => $userId]))
            ->set(NotificationSpaceService::IS_TOUCHED_SETTINGS, true);
    }

    /**
     * @return int[] sorted, distinct
     */
    private function ids(?ActiveQueryUser $query): array
    {
        $ids = array_unique(array_map(fn(User $user) => (int)$user->id, $query?->all() ?? []));
        sort($ids);

        return array_values($ids);
    }

    /**
     * @param Space[] $spaces
     * @return int[] sorted, distinct
     */
    private function spaceIds(array $spaces): array
    {
        $ids = array_unique(array_map(fn(Space $space) => (int)$space->id, $spaces));
        sort($ids);

        return array_values($ids);
    }
}
