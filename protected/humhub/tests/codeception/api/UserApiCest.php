<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\ListEvent;
use humhub\components\listing\QueryListBuilder;
use humhub\libs\BasePermission;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\content\models\ContentContainerTag;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\components\UserList;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\fieldtype\Text;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\permissions\PeopleAccess;
use PHPUnit\Framework\Assert;
use Yii;
use yii\base\Event;

/**
 * The user list (`humhub\modules\user\controllers\api\UserController`) — the user search of the
 * platform, read by the People directory and requiring access to it.
 *
 * Fixture ground truth used here: the enabled users are 1 (`Admin`), 2 (`User1`, Peter), 3
 * (`User2`, Sara), 4 (`User3`, Andreas) and 8 (`AdminNotMember`); 5 is disabled, 6 and 7 are
 * unapproved. Group 2 ("Users", not shown in the directory) has the member 2 - and 4, see
 * `_before()` -, group 3 ("Moderators") the enabled member 3. Space 2 has the one member 2, space 5 is private. User 1 follows user 2; the friendship
 * system is off and its fixture empty.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class UserApiCest
{
    /**
     * @var bool whether `_before()` put user 4 into group 2 — taken out again in `_after()`,
     * since rows the API Cests write outlive them and the unit suites read the same database
     */
    private bool $addedGroupUser = false;

    /**
     * @var bool whether a test added the `gender` profile field ({@see self::genderField()})
     */
    private bool $addedGenderField = false;

    public function _before(): void
    {
        ContentContainerBlockedUsers::deleteAll(['user_id' => 4]);
        // The fixture's one user follow, restored - the follow Cests clear it.
        Follow::deleteAll(['object_model' => User::class]);
        (new Follow(['user_id' => 1, 'object_model' => User::class, 'object_id' => 2]))->save();
        // People is a permission of groups, and the list requires it: user 4 is put into the
        // "Users" group, which is not shown in the directory, so its member lists stay as they are.
        if (!GroupUser::find()->where(['user_id' => 4, 'group_id' => 2])->exists()) {
            Yii::$app->db->createCommand()->insert(GroupUser::tableName(), ['user_id' => 4, 'group_id' => 2])->execute();
            $this->addedGroupUser = true;
        }
        $this->resetSettings();
        $this->removeTags();
    }

    public function _after(): void
    {
        $this->resetSettings();
        $this->removeTags();

        if ($this->addedGroupUser) {
            GroupUser::deleteAll(['user_id' => 4, 'group_id' => 2]);
            $this->addedGroupUser = false;
        }
        if ($this->addedGenderField) {
            ProfileField::deleteAll(['internal_name' => 'gender']);
            $this->addedGenderField = false;
        }
    }

    /**
     * The user tags a test gave - they would outlive it, and a search covers the tags.
     */
    private function removeTags(): void
    {
        foreach ([2, 3, 5] as $id) {
            ContentContainerTagRelation::deleteByContainer(User::findOne($id));
        }
        ContentContainerTag::deleteAll(['contentcontainer_class' => User::class]);
    }

    /**
     * Settings outlive a test's transaction in the cache the requests read them from, so the
     * ones these tests change - and the friendship switch other Cests turn on - start from
     * their defaults and are put back.
     */
    private function resetSettings(): void
    {
        foreach (['people.detail1', 'people.defaultSorting'] as $name) {
            Yii::$app->settings->delete($name);
        }
        Yii::$app->getModule('user')->settings->delete('auth.blockUsers');
        Yii::$app->getModule('user')->settings->delete('auth.hideOnlineStatus');
        Yii::$app->getModule('friendship')->settings->delete('enable');
        foreach ([1, 2, 3, 4] as $id) {
            // The online status lives in the cache for a minute after a user's last request.
            Yii::$app->cache->delete('is_online_user_id_' . $id);
            User::findOne($id)->settings->delete('hideOnlineStatus');
        }
    }

    private function ids(ApiTester $I): array
    {
        return array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    private function sortedIds(ApiTester $I): array
    {
        $ids = $this->ids($I);
        sort($ids);

        return $ids;
    }

    private function enableFriendship(bool $enable = true): void
    {
        Yii::$app->getModule('friendship')->settings->set('enable', $enable ? 1 : 0);
    }

    private function seedRequest(int $from, int $to): void
    {
        Yii::$app->db->createCommand()->insert('user_friendship', [
            'user_id' => $from,
            'friend_user_id' => $to,
            'created_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    private function genderField(): void
    {
        Yii::$app->db->createCommand()->insert(ProfileField::tableName(), [
            'profile_field_category_id' => 1,
            'field_type_class' => Select::class,
            'field_type_config' => json_encode(['options' => "male=>Male\nfemale=>Female"]),
            'internal_name' => 'gender',
            'title' => 'Gender',
            'visible' => 1,
            'directory_filter' => 1,
        ])->execute();
        $this->addedGenderField = true;
    }

    private function denyPeopleAccess(int $groupId, bool $deny = true): void
    {
        (new PermissionManager())->setGroupState($groupId, PeopleAccess::class, $deny ? BasePermission::STATE_DENY : null);
    }

    public function testListsOnlyAvailableUsers(ApiTester $I)
    {
        $I->wantTo('list the users I may see');
        $I->amLoggedInAs(4);
        $I->sendGet('user');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        Assert::assertSame([1, 2, 3, 4, 8], $this->sortedIds($I), 'no disabled or unapproved users');

        // The paginated list envelope of this API generation.
        $I->seeResponseJsonMatchesJsonPath('$.total');
        $I->seeResponseJsonMatchesJsonPath('$.page');
        $I->seeResponseJsonMatchesJsonPath('$.pageSize');
        $I->seeResponseJsonMatchesJsonPath('$.pages');
    }

    public function testLeavesHiddenAndBlockingUsersOut(ApiTester $I)
    {
        $I->wantTo('not see hidden users or users who blocked me');
        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => User::findOne(['id' => 2])->contentcontainer_id,
            'user_id' => 4,
        ])->execute();

        $I->amLoggedInAs(4);
        $I->sendGet('user');

        $I->seeResponseCodeIs(200);
        Assert::assertSame([1, 4, 8], $this->sortedIds($I));
    }

    public function testCarriesTheListShape(ApiTester $I)
    {
        $I->wantTo('read what a listed user carries');
        Yii::$app->settings->set('people.detail1', 'lastname');

        $I->amLoggedInAs(4);
        $I->sendGet('user', ['q' => 'Sara']);

        $I->seeResponseCodeIs(200);
        $user = json_decode($I->grabResponse(), true)['results'][0];

        Assert::assertSame(
            ['id', 'guid', 'displayName', 'url', 'imageUrl', 'contentContainerId', 'bannerUrl', 'title', 'details', 'tags', 'followerCount', 'friendCount'],
            array_keys($user),
        );
        Assert::assertSame(3, $user['id']);
        Assert::assertSame('Sara Tester', $user['displayName']);
        Assert::assertStringStartsWith('http', $user['url'], 'an absolute URL');
        Assert::assertSame(['Tester'], $user['details'], "the administrator's card field");
        Assert::assertSame([], $user['tags']);
        Assert::assertNull($user['bannerUrl']);
        // Caller context is not part of this shape - it is the same for everyone asking.
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].isFollowing');
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].friendship');
    }

    public function testCarriesFollowerAndFriendCounts(ApiTester $I)
    {
        $I->wantTo('see how many followers and friends a listed user has');
        $I->amLoggedInAs(4);

        $I->sendGet('user', ['ids' => [2, 3]]);
        $I->seeResponseCodeIs(200);
        $byId = array_column(json_decode($I->grabResponse(), true)['results'], null, 'id');
        Assert::assertSame(1, $byId[2]['followerCount'], 'user 1 follows user 2');
        Assert::assertSame(0, $byId[3]['followerCount']);
        Assert::assertNull($byId[2]['friendCount'], 'no count while the friendship system is off');

        $this->enableFriendship();
        $this->seedRequest(2, 3);
        $this->seedRequest(3, 2);
        $I->sendGet('user', ['ids' => [2, 3]]);
        $byId = array_column(json_decode($I->grabResponse(), true)['results'], null, 'id');
        Assert::assertSame(1, $byId[2]['friendCount']);
        Assert::assertSame(1, $byId[3]['friendCount']);

        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;
        try {
            $I->sendGet('user', ['ids' => [2]]);
            Assert::assertNull(json_decode($I->grabResponse(), true)['results'][0]['followerCount'], 'no count while following is disabled');
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testSearches(ApiTester $I)
    {
        $I->wantTo('search users by name');
        $I->amLoggedInAs(4);
        $I->sendGet('user', ['q' => 'Peter']);

        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I));
    }

    public function testScopesToFollows(ApiTester $I)
    {
        $I->wantTo('list the users I follow, and my followers');
        $I->amLoggedInAs(1);

        $I->sendGet('user', ['scope' => 'following']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I));

        $I->sendGet('user', ['scope' => 'followers']);
        Assert::assertSame([], $this->ids($I));
    }

    public function testScopesToFriends(ApiTester $I)
    {
        $I->wantTo('list my friends and the requests I sent');
        $this->enableFriendship();
        $this->seedRequest(4, 2);
        $this->seedRequest(2, 4);
        $this->seedRequest(4, 3);
        $this->seedRequest(1, 4);

        $I->amLoggedInAs(4);
        $I->sendGet('user', ['scope' => 'friends']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I));

        $I->sendGet('user', ['scope' => 'pendingFriends']);
        Assert::assertSame([3], $this->ids($I), 'not the request I received');
    }

    public function testRefusesScopesOfAFeatureThatIsOff(ApiTester $I)
    {
        $I->wantTo('be told a scope is not available');
        $I->amLoggedInAs(4);

        $I->sendGet('user', ['scope' => 'friends']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.scope');

        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;
        try {
            $I->sendGet('user', ['scope' => 'following']);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.scope');
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testFiltersByGroupAndSpace(ApiTester $I)
    {
        $I->wantTo('list the members of a group or a space');
        $I->amLoggedInAs(4);

        $I->sendGet('user', ['groupId' => 3]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([3], $this->ids($I));

        $I->sendGet('user', ['spaceId' => 2]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I));
    }

    public function testRefusesGroupsAndSpacesTheCallerMayNotUse(ApiTester $I)
    {
        $I->wantTo('be refused a hidden group, a private space and unknown ones');
        Yii::$app->db->createCommand()->update('group', ['show_at_directory' => 0], ['id' => 2])->execute();

        $I->amLoggedInAs(4);
        foreach ([['groupId' => 2], ['groupId' => 99999], ['groupId' => 'x'], ['spaceId' => 5], ['spaceId' => 99999]] as $params) {
            $I->sendGet('user', $params);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.' . array_key_first($params));
        }
    }

    public function testFiltersByProfileFields(ApiTester $I)
    {
        $I->wantTo('filter users by a directory profile field');
        $this->genderField();
        Profile::updateAll(['gender' => 'female'], ['user_id' => [2, 3]]);

        $I->amLoggedInAs(4);
        $I->sendGet('user?fields[gender]=female');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2, 3], $this->sortedIds($I));

        $I->sendGet('user?fields[gender]=');
        Assert::assertCount(5, $this->ids($I), 'an empty value is no restriction');

        // A field that is not a directory filter is refused, under the filter's own key.
        $I->sendGet('user?fields[firstname]=Sara');
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('fields[firstname]', json_decode($I->grabResponse(), true)['errors']);
    }

    public function testSortsByTheRequestedOrder(ApiTester $I)
    {
        $I->wantTo('sort the user list');
        foreach ([1 => '2020-01-03', 2 => '2020-01-05', 3 => null, 4 => '2020-01-01', 8 => '2020-01-04'] as $id => $lastLogin) {
            User::updateAll(['last_login' => $lastLogin], ['id' => $id]);
        }

        $I->amLoggedInAs(4);
        $I->sendGet('user', ['sort' => 'firstname']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([1, 8, 4, 2, 3], $this->ids($I));

        $I->sendGet('user', ['sort' => 'lastname']);
        Assert::assertSame([1, 4, 2, 3, 8], $this->ids($I));

        $I->sendGet('user', ['sort' => 'lastlogin']);
        Assert::assertSame([4, 2, 8, 1, 3], $this->ids($I), 'logging in made User3 the most recent one, never logged in comes last');

        Yii::$app->settings->set('people.defaultSorting', 'lastname');
        $I->sendGet('user');
        Assert::assertSame([1, 4, 2, 3, 8], $this->ids($I), "without a sort the administrator's order");
    }

    public function testNarrowsToIdsAndExcludes(ApiTester $I)
    {
        $I->wantTo('ask for named users only, or leave some out');
        $I->amLoggedInAs(4);

        $I->sendGet('user?ids=2,3,5');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2, 3], $this->sortedIds($I), 'ids never widen availability');

        $I->sendGet('user', ['ids' => [2, 3], 'exclude' => [3]]);
        Assert::assertSame([2], $this->ids($I));

        $I->sendGet('user?exclude=1,2');
        Assert::assertSame([3, 4, 8], $this->sortedIds($I));
    }

    public function testRejectsUnknownValues(ApiTester $I)
    {
        $I->wantTo('be told about a parameter value the list does not know');
        $I->amLoggedInAs(4);

        foreach ([
            'scope' => ['scope' => 'members'],
            'sort' => ['sort' => 'random'],
            'purpose' => ['purpose' => 'stream'],
            'ids' => ['ids' => 'abc'],
            'exclude' => ['exclude' => '1,x'],
        ] as $name => $params) {
            $I->sendGet('user', $params);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.' . $name);
        }
    }

    public function testCapsTheIdLists(ApiTester $I)
    {
        $I->wantTo('be refused naming more than 100 users');
        $I->amLoggedInAs(4);

        $I->sendGet('user', ['ids' => implode(',', range(1, 101))]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.ids');

        $I->sendGet('user', ['exclude' => range(1, 101)]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.exclude');

        $I->sendGet('user', ['ids' => implode(',', range(1, 100))]);
        $I->seeResponseCodeIs(200);
    }

    public function testCapsThePageSize(ApiTester $I)
    {
        $I->wantTo('not get more than 100 users per page');
        $I->amLoggedInAs(4);
        $I->sendGet('user', ['pageSize' => 1000]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(100, $I->grabDataFromResponseByJsonPath('$.pageSize')[0]);

        $I->sendGet('user');
        Assert::assertSame(25, $I->grabDataFromResponseByJsonPath('$.pageSize')[0]);

        $I->sendGet('user', ['pageSize' => 2, 'page' => 2, 'sort' => 'firstname']);
        Assert::assertSame([4, 2], $this->ids($I), 'the second page');
        Assert::assertSame(5, $I->grabDataFromResponseByJsonPath('$.total')[0]);
    }

    public function testTheListRequiresPeopleAccess(ApiTester $I)
    {
        $I->wantTo('be refused the list without access to People, whatever the purpose');
        $this->denyPeopleAccess(2);

        try {
            $I->amLoggedInAs(2);
            foreach ([['purpose' => 'directory'], ['purpose' => 'picker'], [], ['scope' => 'nothing']] as $params) {
                $I->sendGet('user', $params);
                $I->seeResponseCodeIs(403);
            }
        } finally {
            $this->denyPeopleAccess(2, false);
        }
    }

    public function testRefusesUnknownParameters(ApiTester $I)
    {
        $I->wantTo('be told about a parameter the list does not know');
        $I->amLoggedInAs(4);

        $I->sendGet('user', ['interest' => 'sara']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.interest');

        // Empty is absent for a known parameter, not for an unknown one.
        $I->sendGet('user', ['q' => '', 'groupId' => '', 'spaceId' => '']);
        $I->seeResponseCodeIs(200);
    }

    public function testModulesAddAFilterAndRestrictByPurpose(ApiTester $I)
    {
        $I->wantTo('let a module add its own filter and restrict the list by the purpose');
        $received = null;
        $init = static function (ListEvent $event) {
            $event->list->addFilter(new EnumFilter(
                'interest',
                values: ['sara' => 'Sara'],
                apply: static fn(QueryListBuilder $list) => $list->query()->andWhere(['user.id' => [2, 3]]),
            ));
        };
        $build = static function (ListEvent $event) use (&$received) {
            $received = ['purpose' => $event->context->purpose, 'interest' => $event->value('interest')];
            if ($event->context->purpose === UserList::PURPOSE_MENTIONING) {
                $event->builder->query()->andWhere(['user.id' => 3]);
            }
        };
        Event::on(UserList::class, UserList::EVENT_INIT, $init);
        Event::on(UserList::class, UserList::EVENT_BUILD, $build);

        try {
            $I->amLoggedInAs(4);
            $I->sendGet('user', ['interest' => 'sara', 'purpose' => 'mentioning']);
            $I->seeResponseCodeIs(200);
            Assert::assertSame(['purpose' => 'mentioning', 'interest' => 'sara'], $received);
            Assert::assertSame([3], $this->ids($I), 'the handler restricted the list');

            $I->sendGet('user', ['interest' => 'sara']);
            $I->seeResponseCodeIs(200);
            Assert::assertNull($received['purpose'], 'absent purpose is neutral');
            Assert::assertSame([2, 3], $this->sortedIds($I), "the module's filter");

            $I->sendGet('user', ['interest' => 'bob']);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.interest');
        } finally {
            Event::off(UserList::class, UserList::EVENT_INIT, $init);
            Event::off(UserList::class, UserList::EVENT_BUILD, $build);
        }
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->wantTo('be rejected without a session');
        foreach (['user', 'user/states', 'user/field-values?field=gender', 'user/tags'] as $url) {
            $I->sendGet($url);
            $I->seeResponseCodeIs(401);
        }
    }

    public function testStatesTellWhatTheCallerIsToTheUsers(ApiTester $I)
    {
        $I->wantTo('learn per user whether it is me, whether I follow them and may follow them');
        $I->amLoggedInAs(1);
        $I->sendGet('user/states', ['ids' => [1, 2, 3]]);

        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];

        Assert::assertSame(['isSelf' => true, 'isFollowing' => false, 'canFollow' => false, 'friendship' => null, 'isOnline' => null], $states[1]);
        Assert::assertSame(['isSelf' => false, 'isFollowing' => true, 'canFollow' => true, 'friendship' => null, 'isOnline' => false], $states[2], 'user 1 follows user 2');
        Assert::assertFalse($states[3]['isFollowing']);
        Assert::assertNull($states[3]['friendship'], 'no friendship while the system is off');
    }

    public function testStatesCarryTheFriendshipShape(ApiTester $I)
    {
        $I->wantTo('learn my friendship with the users I am shown, as the friendship API tells it');
        $this->enableFriendship();
        $this->seedRequest(3, 2);

        $I->amLoggedInAs(2);
        $I->sendGet('user/states?ids=2,3,4');
        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];

        Assert::assertNull($states[2]['friendship'], 'no friendship with oneself');
        Assert::assertSame('requestReceived', $states[3]['friendship']['state']);
        Assert::assertSame('none', $states[4]['friendship']['state']);

        $I->sendGet('user/3/friendship');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(json_decode($I->grabResponse(), true), $states[3]['friendship'], 'the friendship route is still reached');
    }

    public function testStatesTellWhenFollowingIsDisabled(ApiTester $I)
    {
        $I->wantTo('not be offered following while it is disabled');
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;

        try {
            $I->amLoggedInAs(4);
            $I->sendGet('user/states', ['ids' => [2]]);
            $I->seeResponseCodeIs(200);
            Assert::assertFalse(json_decode($I->grabResponse(), true)['results'][2]['canFollow']);
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testStatesTellWhetherTheUsersAreOnline(ApiTester $I)
    {
        $I->wantTo('learn who of the users I am shown is online, but not about myself');
        Yii::$app->cache->set('is_online_user_id_2', true, 60);

        $I->amLoggedInAs(4);
        $I->sendGet('user/states', ['ids' => [2, 3, 4]]);
        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];

        Assert::assertTrue($states[2]['isOnline']);
        Assert::assertFalse($states[3]['isOnline']);
        Assert::assertNull($states[4]['isOnline'], 'no online status of oneself');
    }

    public function testStatesLeaveOutAnOnlineStatusThatIsHidden(ApiTester $I)
    {
        $I->wantTo('learn no online status of a user who hides it, and none while the administrator hides it');
        Yii::$app->cache->set('is_online_user_id_2', true, 60);
        Yii::$app->cache->set('is_online_user_id_3', true, 60);
        User::findOne(3)->settings->set('hideOnlineStatus', 1);

        $I->amLoggedInAs(4);
        $I->sendGet('user/states', ['ids' => [2, 3]]);
        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];
        Assert::assertTrue($states[2]['isOnline']);
        Assert::assertArrayHasKey('isOnline', $states[3]);
        Assert::assertNull($states[3]['isOnline'], 'user 3 hides their online status');

        Yii::$app->getModule('user')->settings->set('auth.hideOnlineStatus', 1);
        $I->sendGet('user/states', ['ids' => [2, 3]]);
        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];
        Assert::assertNull($states[2]['isOnline']);
        Assert::assertNull($states[3]['isOnline']);
    }

    public function testStatesLeaveOutUsersTheCallerMayNotSee(ApiTester $I)
    {
        $I->wantTo('learn nothing about users I may not see');
        $I->amLoggedInAs(4);
        $I->sendGet('user/states', ['ids' => [2, 5, 99999]]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['2'], array_map('strval', array_keys(json_decode($I->grabResponse(), true)['results'])));
    }

    public function testStatesLeaveOutHiddenUsers(ApiTester $I)
    {
        $I->wantTo('learn nothing about hidden users, as the directory never lists them - not even as an administrator');
        User::updateAll(['visibility' => User::VISIBILITY_HIDDEN], ['id' => 3]);

        $I->amLoggedInAs(1);
        $I->sendGet('user/states', ['ids' => [2, 3]]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['2'], array_map('strval', array_keys(json_decode($I->grabResponse(), true)['results'])));
    }

    public function testStatesRequirePeopleAccess(ApiTester $I)
    {
        $I->wantTo('be refused the states without access to People');
        $this->denyPeopleAccess(2);

        try {
            $I->amLoggedInAs(2);
            $I->sendGet('user/states', ['ids' => [3]]);
            $I->seeResponseCodeIs(403);
        } finally {
            $this->denyPeopleAccess(2, false);
        }
    }

    public function testStatesAnswerAnEmptyMapWithoutIds(ApiTester $I)
    {
        $I->wantTo('ask for no states at all');
        $I->amLoggedInAs(4);
        $I->sendGet('user/states');

        $I->seeResponseCodeIs(200);
        Assert::assertSame('{"results":{}}', $I->grabResponse());
    }

    public function testFieldValuesOfADropdown(ApiTester $I)
    {
        $I->wantTo('load the options of a profile field filter');
        $this->genderField();

        // User3 is in no group, and People is a permission of groups.
        $I->amLoggedInAs(2);
        $I->sendGet('user/field-values', ['field' => 'gender']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(
            ['results' => [['id' => 'male', 'name' => 'Male'], ['id' => 'female', 'name' => 'Female']]],
            json_decode($I->grabResponse(), true),
        );
    }

    public function testFieldValuesOfATextField(ApiTester $I)
    {
        $I->wantTo('load what users entered into a text field');
        ProfileField::updateAll(['directory_filter' => 1, 'field_type_class' => Text::class], ['internal_name' => 'mobile']);
        Profile::updateAll(['mobile' => '555'], ['user_id' => [2, 3]]);
        Profile::updateAll(['mobile' => '123'], ['user_id' => 4]);

        $I->amLoggedInAs(2);
        $I->sendGet('user/field-values', ['field' => 'mobile']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(
            [['id' => '555', 'name' => '555', 'count' => 2], ['id' => '123', 'name' => '123', 'count' => 1]],
            json_decode($I->grabResponse(), true)['results'],
            'the most frequent first, numeric values as strings',
        );
    }

    public function testFieldValuesSearch(ApiTester $I)
    {
        $I->wantTo('search the values of a profile field filter as a picker user types');
        $this->genderField();
        ProfileField::updateAll(['directory_filter' => 1, 'field_type_class' => Text::class], ['internal_name' => 'mobile']);
        Profile::updateAll(['mobile' => '555-1'], ['user_id' => [2, 3]]);
        Profile::updateAll(['mobile' => '123'], ['user_id' => 4]);

        $I->amLoggedInAs(2);
        $I->sendGet('user/field-values', ['field' => 'mobile', 'q' => '55']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([['id' => '555-1', 'name' => '555-1', 'count' => 2]], json_decode($I->grabResponse(), true)['results']);

        $I->sendGet('user/field-values', ['field' => 'mobile', 'q' => 'nothing']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame('{"results":[]}', $I->grabResponse());

        // The static options of a dropdown, narrowed by their label.
        $I->sendGet('user/field-values', ['field' => 'gender', 'q' => 'fem']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([['id' => 'female', 'name' => 'Female']], json_decode($I->grabResponse(), true)['results']);
    }

    public function testTagsAreTheTopTagsOfTheUsersICanSee(ApiTester $I)
    {
        $I->wantTo('load the user tags for the tag picker, searched as I type');
        $this->tag(2, ['PHP', 'Go']);
        $this->tag(3, ['PHP']);
        $this->tag(5, ['Secret']);

        $I->amLoggedInAs(2);
        $I->sendGet('user/tags');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(
            ['results' => [['id' => 'PHP', 'name' => 'PHP', 'count' => 2], ['id' => 'Go', 'name' => 'Go', 'count' => 1]]],
            json_decode($I->grabResponse(), true),
            'nothing of the disabled user 5',
        );

        $I->sendGet('user/tags', ['q' => 'g']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([['id' => 'Go', 'name' => 'Go', 'count' => 1]], json_decode($I->grabResponse(), true)['results']);
    }

    public function testFiltersByTags(ApiTester $I)
    {
        $I->wantTo('list the users having all the given tags');
        $this->tag(2, ['PHP', 'Go']);
        $this->tag(3, ['PHP']);

        $I->amLoggedInAs(2);
        $I->sendGet('user', ['tag' => ['PHP']]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2, 3], $this->sortedIds($I));

        $I->sendGet('user?tag=PHP&tag=Go');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I), 'repeated');

        $I->sendGet('user?tag=php,go');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2], $this->ids($I), 'comma-separated, ignoring case');
    }

    public function testCapsTheSearchAndTheTags(ApiTester $I)
    {
        $I->wantTo('be refused a search longer than 255 characters and more than 20 tags');
        ProfileField::updateAll(['directory_filter' => 1, 'field_type_class' => Text::class], ['internal_name' => 'mobile']);
        $I->amLoggedInAs(2);

        foreach (['user', 'user/tags', 'user/field-values?field=mobile'] as $url) {
            $I->sendGet($url, ['q' => str_repeat('x', 256)]);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.q');

            $I->sendGet($url, ['q' => str_repeat('x', 255)]);
            $I->seeResponseCodeIs(200);
        }

        $I->sendGet('user', ['tag' => implode(',', range(1, 21))]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.tag');

        $I->sendGet('user', ['tag' => range(1, 20)]);
        $I->seeResponseCodeIs(200);
    }

    public function testSuggestionsFollowTheDirectoryRestrictionsOfModules(ApiTester $I)
    {
        $I->wantTo('get no tags and field values of a user a module hides from the directory');
        ProfileField::updateAll(['directory_filter' => 1, 'field_type_class' => Text::class], ['internal_name' => 'mobile']);
        Profile::updateAll(['mobile' => '555'], ['user_id' => 2]);
        Profile::updateAll(['mobile' => '777'], ['user_id' => 3]);
        $this->tag(2, ['PHP']);
        $this->tag(3, ['Secret']);
        $build = static function (ListEvent $event) {
            if ($event->context->purpose === UserList::PURPOSE_DIRECTORY) {
                $event->builder->query()->andWhere(['!=', 'user.id', 3]);
            }
        };
        Event::on(UserList::class, UserList::EVENT_BUILD, $build);

        try {
            $I->amLoggedInAs(2);
            $I->sendGet('user/tags');
            $I->seeResponseCodeIs(200);
            Assert::assertSame(['PHP'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

            $I->sendGet('user/tags', ['q' => 'sec']);
            Assert::assertSame('{"results":[]}', $I->grabResponse());

            $I->sendGet('user/field-values', ['field' => 'mobile']);
            $I->seeResponseCodeIs(200);
            Assert::assertSame(['555'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

            $I->sendGet('user/field-values', ['field' => 'mobile', 'q' => '77']);
            Assert::assertSame('{"results":[]}', $I->grabResponse());
        } finally {
            Event::off(UserList::class, UserList::EVENT_BUILD, $build);
        }
    }

    /**
     * @param string[] $tags
     */
    private function tag(int $userId, array $tags): void
    {
        ContentContainerTagRelation::updateByContainer(User::findOne($userId), $tags);
    }

    public function testFieldValuesRefuseOtherFields(ApiTester $I)
    {
        $I->wantTo('be refused the values of a field that is no directory filter');
        $I->amLoggedInAs(2);

        $I->sendGet('user/field-values');
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.field');

        foreach (['firstname', 'nothing'] as $field) {
            $I->sendGet('user/field-values', ['field' => $field]);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.field');
        }
    }

    public function testFieldsOfAnUnsupportedTypeOrInvisibleAreNoFilters(ApiTester $I)
    {
        $I->wantTo('neither filter by nor list the values of a birthday or an invisible field');
        ProfileField::updateAll(['directory_filter' => 1], ['internal_name' => ['birthday', 'mobile']]);
        ProfileField::updateAll(['visible' => 0], ['internal_name' => 'mobile']);

        $I->amLoggedInAs(2);
        foreach (['birthday', 'mobile'] as $field) {
            $I->sendGet('user/field-values', ['field' => $field]);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.field');

            $I->sendGet('user?fields[' . $field . ']=1');
            $I->seeResponseCodeIs(422);
            Assert::assertArrayHasKey('fields[' . $field . ']', json_decode($I->grabResponse(), true)['errors']);
        }
    }

    public function testFieldValuesRequirePeopleAccess(ApiTester $I)
    {
        $I->wantTo('be refused the directory filter values and the tags without access to People');
        $this->genderField();
        $this->denyPeopleAccess(2);

        try {
            $I->amLoggedInAs(2);
            // Refused before the field is looked at, so the field names do not leak either.
            foreach (['gender', 'nothing'] as $field) {
                $I->sendGet('user/field-values', ['field' => $field]);
                $I->seeResponseCodeIs(403);
            }
            $I->sendGet('user/tags');
            $I->seeResponseCodeIs(403);
        } finally {
            $this->denyPeopleAccess(2, false);
        }
    }
}
