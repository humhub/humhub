<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\user;

use humhub\modules\friendship\serializers\FriendshipSerializer;
use humhub\modules\user\models\fieldtype\Text;
use humhub\modules\user\models\fieldtype\UserEmail;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\UserSerializer;
use humhub\modules\user\services\IsOnlineService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The batch paths of the user serializers answer a page with a fixed number of queries,
 * however many users it has — and answer what the single paths answer.
 *
 * Fixture ground truth: the friendship fixture is empty; follows between users are cleared
 * before every test (other suites change them) and seeded where a test needs them.
 *
 * @since 1.20
 */
class UserStatesBatchTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Follow::deleteAll(['object_model' => User::class]);
    }

    /**
     * @return int the database queries `$call` ran
     */
    private function queries(callable $call): int
    {
        // The server's own statement counter of this session - independent of logging setup.
        $count = fn() => (int)Yii::$app->db->createCommand("SHOW SESSION STATUS LIKE 'Questions'")->queryOne()['Value'];

        $before = $count();
        $call();

        // The second SHOW counts itself.
        return $count() - $before - 1;
    }

    /**
     * The users as a list page loads them: with their profile and container.
     */
    private function users(array $ids): array
    {
        return User::find()->with(['profile', 'contentContainerRecord'])->where(['id' => $ids])->orderBy('id')->all();
    }

    private function seedRequest(int $from, int $to): void
    {
        Yii::$app->db->createCommand()->insert('user_friendship', [
            'user_id' => $from,
            'friend_user_id' => $to,
            'created_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    /**
     * The profile's `title` column as the field the name's subtitle shows - the installer's
     * default, which the fixture has no row for.
     */
    private function titleField(): void
    {
        Yii::$app->db->createCommand()->insert(ProfileField::tableName(), [
            'profile_field_category_id' => 1,
            'field_type_class' => Text::class,
            'internal_name' => 'title',
            'title' => 'Title',
            'visible' => 1,
        ])->execute();
        Yii::$app->settings->set('displayNameSubFormat', 'title');
    }

    private function warmUp(): void
    {
        // Settings, the identity and other per-request lookups are loaded once, whatever the
        // batch size - load them before measuring.
        UserSerializer::batch($this->users([1, 2, 3, 4]));
        FriendshipSerializer::states($this->users([1, 2, 3, 4]));
        Yii::$app->runtimeCache->flush();
    }

    public function testCountsFollowersAndFriends()
    {
        $this->enableFriendships();
        $follow = new Follow(['user_id' => 1, 'object_model' => User::class, 'object_id' => 2]);
        $this->assertTrue($follow->save());
        $this->seedRequest(3, 2);
        $this->seedRequest(2, 3);
        $this->seedRequest(4, 2);
        // A disabled user's friendship does not count.
        $this->seedRequest(5, 2);
        $this->seedRequest(2, 5);

        $counts = UserSerializer::counts($this->users([2, 3, 4]));

        $this->assertSame(['followerCount' => 1, 'friendCount' => 1], $counts[2], 'followed by user 1, friends with user 3 - a request is no friendship');
        $this->assertSame(['followerCount' => 0, 'friendCount' => 1], $counts[3]);
        $this->assertSame(['followerCount' => 0, 'friendCount' => 0], $counts[4]);
    }

    public function testCountsAreNullWhileTheFeatureIsOff()
    {
        $this->enableFriendships(false);
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;

        try {
            $this->assertSame(['followerCount' => null, 'friendCount' => null], UserSerializer::counts($this->users([2]))[2]);
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testCountsDoNotQueryPerUser()
    {
        $this->enableFriendships();
        $this->becomeUser('User1');
        $this->warmUp();

        $this->assertSame(
            $this->queries(fn() => UserSerializer::counts($this->users([1]))),
            $this->queries(fn() => UserSerializer::counts($this->users([1, 2, 3, 4]))),
        );
    }

    public function testBatchDoesNotQueryPerUser()
    {
        $this->enableFriendships();
        $this->becomeUser('User1');
        $this->titleField();
        Yii::$app->settings->set('people.detail1', 'lastname');
        Yii::$app->settings->set('people.detail2', 'mobile');
        $this->warmUp();

        $one = $this->users([1]);
        $many = $this->users([1, 2, 3, 4]);

        $this->assertSame(
            $this->queries(fn() => UserSerializer::batch($one)),
            $this->queries(fn() => UserSerializer::batch($many)),
        );
    }

    public function testListCarriesTheCardFields()
    {
        $this->becomeUser('User1');
        $this->titleField();
        Yii::$app->settings->set('people.detail1', 'lastname');
        Yii::$app->settings->set('people.detail2', 'mobile');
        Yii::$app->settings->set('people.detail3', 'url');
        ProfileField::updateAll(['field_type_config' => json_encode(['validator' => 'url'])], ['internal_name' => 'url']);
        Profile::updateAll(['url' => 'https://example.com/<b>', 'mobile' => ''], ['user_id' => 3]);
        Profile::updateAll(['lastname' => str_repeat('x', 250)], ['user_id' => 4]);

        [$sara, $andreas] = UserSerializer::batch($this->users([3, 4]));

        $this->assertSame('Test Master', $sara['title']);
        $this->assertSame(['Tester', 'https://example.com/<b>'], $sara['details'], 'plain text - a URL field renders a link - and the empty mobile left out');
        $this->assertNull($andreas['title']);
        $this->assertSame(203, mb_strlen($andreas['details'][0]), 'truncated to 200 characters and the ellipsis');
        $this->assertNull($sara['bannerUrl']);
        $this->assertSame([], $sara['tags']);

        ProfileField::updateAll(['visible' => 0], ['internal_name' => 'lastname']);
        $this->assertSame(['https://example.com/<b>'], UserSerializer::list($this->users([3])[0])['details'], 'a field that is not visible is no detail');
    }

    public function testCardFieldsArePlainTextOfLinkingFields()
    {
        $this->becomeUser('User1');
        Yii::$app->db->createCommand()->insert(ProfileField::tableName(), [
            'profile_field_category_id' => 1,
            'field_type_class' => UserEmail::class,
            'internal_name' => 'test_email',
            'title' => 'E-Mail',
            'visible' => 1,
        ])->execute();
        Yii::$app->settings->set('displayNameSubFormat', 'test_email');
        Yii::$app->settings->set('people.detail1', 'mobile');
        ProfileField::updateAll(['field_type_config' => json_encode(['validator' => 'email'])], ['internal_name' => 'mobile']);
        Profile::updateAll(['mobile' => 'sara&co@example.com'], ['user_id' => 3]);
        User::updateAll(['email' => 'sara@example.com'], ['id' => 3]);

        $sara = UserSerializer::list($this->users([3])[0]);

        // Both render as `<a href="mailto:...">`, the card gets the text.
        $this->assertSame('sara@example.com', $sara['title']);
        $this->assertSame(['sara&co@example.com'], $sara['details']);
    }

    public function testFriendshipStatesMatchTheSingleState()
    {
        $this->enableFriendships();
        $this->becomeUser('User1');
        $this->seedRequest(2, 3);
        $this->seedRequest(3, 2);
        $this->seedRequest(2, 4);
        $this->seedRequest(8, 2);

        $states = FriendshipSerializer::states($this->users([1, 3, 4, 8]));

        $this->assertSame('friends', $states[3]['state']);
        $this->assertSame('requestSent', $states[4]['state']);
        $this->assertSame('requestReceived', $states[8]['state']);
        $this->assertSame('none', $states[1]['state']);

        foreach ($this->users([1, 3, 4, 8]) as $user) {
            $this->assertSame(FriendshipSerializer::state($user), $states[$user->id]);
        }
    }

    public function testFriendshipStatesDoNotQueryPerUser()
    {
        $this->enableFriendships();
        $this->becomeUser('User1');
        $this->warmUp();

        $one = $this->users([3]);
        $many = $this->users([1, 3, 4, 8]);

        $forOne = $this->queries(fn() => FriendshipSerializer::states($one));
        $this->assertGreaterThan(0, $forOne, 'the measurement sees queries');
        $this->assertSame($forOne, $this->queries(fn() => FriendshipSerializer::states($many)));
    }

    public function testFriendshipStatesTakeTheFollowsTheCallerLoaded()
    {
        $this->enableFriendships();
        $this->becomeUser('User1');
        $this->warmUp();
        $users = $this->users([1, 3, 4]);

        $loaded = $this->queries(fn() => FriendshipSerializer::states($users, [4]));
        $this->assertSame($this->queries(fn() => FriendshipSerializer::states($users)) - 1, $loaded, 'no follow query');

        $states = FriendshipSerializer::states($users, [4]);
        $this->assertTrue($states[4]['isFollowing']);
        $this->assertFalse($states[3]['isFollowing']);
    }

    public function testOnlineStatusesMatchTheSingleStatus()
    {
        Yii::$app->getModule('user')->settings->delete('auth.hideOnlineStatus');
        $users = $this->users([1, 2, 3, 4]);
        foreach ($users as $user) {
            Yii::$app->cache->delete('is_online_user_id_' . $user->id);
            $user->settings->delete('hideOnlineStatus');
        }
        Yii::$app->cache->set('is_online_user_id_2', true, 60);
        Yii::$app->cache->set('is_online_user_id_4', true, 60);
        $users[3]->settings->set('hideOnlineStatus', 1);

        $statuses = IsOnlineService::getStatuses($users);

        $this->assertSame([1 => false, 2 => true, 3 => false, 4 => null], $statuses);
        foreach ($users as $user) {
            $service = new IsOnlineService($user);
            $this->assertSame($service->isEnabled() ? $service->getStatus() : null, $statuses[$user->id], 'user ' . $user->id);
        }

        $this->assertSame(1, $this->queries(fn() => IsOnlineService::getStatuses($users)), 'one settings query for all users');

        Yii::$app->getModule('user')->settings->set('auth.hideOnlineStatus', 1);
        try {
            $this->assertSame([1 => null, 2 => null, 3 => null, 4 => null], IsOnlineService::getStatuses($users));
        } finally {
            Yii::$app->getModule('user')->settings->delete('auth.hideOnlineStatus');
            $users[3]->settings->delete('hideOnlineStatus');
        }
        $this->assertSame([], IsOnlineService::getStatuses([]));
    }
}
