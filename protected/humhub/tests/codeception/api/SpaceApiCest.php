<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\components\SpaceListQuery;
use humhub\modules\space\components\SpaceListQueryEvent;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use PHPUnit\Framework\Assert;
use Yii;
use yii\base\Event;

/**
 * The space list (`humhub\modules\space\controllers\api\SpaceController`) — the general one,
 * read by the space chooser island and open to any other consumer.
 *
 * Fixture ground truth used here: spaces 2, 3 and 4 are public, space 1 is visible to
 * registered users, space 5 is private. User 1 is a member of spaces 1, 3, 4 and 5; user 4
 * (`User3`) is a member of nothing.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class SpaceApiCest
{
    private function guid(int $id): string
    {
        return Space::findOne(['id' => $id])->guid;
    }

    private function names(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].name');
    }

    public function testListsOnlyWhatTheCallerMaySee(ApiTester $I)
    {
        $I->wantTo('list the spaces I may see');
        $I->amLoggedInAs(4);
        $I->sendGet('space');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $names = $this->names($I);
        Assert::assertContains('Space 2', $names, 'a public space is listed');
        Assert::assertNotContains(
            'Space 5',
            $names,
            'a private space is not listed for someone who is not a member',
        );

        // The paginated list envelope of this API generation.
        $I->seeResponseJsonMatchesJsonPath('$.total');
        $I->seeResponseJsonMatchesJsonPath('$.page');
        $I->seeResponseJsonMatchesJsonPath('$.pageSize');
        $I->seeResponseJsonMatchesJsonPath('$.pages');
    }

    public function testCarriesTheListShape(ApiTester $I)
    {
        $I->wantTo('read what a listed space carries');
        $I->amLoggedInAs(1);
        $I->sendGet('space', ['q' => 'Space 2']);

        $I->seeResponseCodeIs(200);
        // "Space 2" also matches Space 4, whose description reads "User 1/2 Space" - the
        // search takes each keyword and looks in every searchable field.
        Assert::assertContains('Space 2', $this->names($I), 'the search finds the space by name');
        Assert::assertNotContains('Space 1', $this->names($I), 'and narrows the list');

        $I->seeResponseJsonMatchesJsonPath('$.results[0].guid');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].url');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].contentContainerId');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].description');
        // Enums are named values, not the stored integers
        Assert::assertContains(
            $I->grabDataFromResponseByJsonPath('$.results[0].visibility')[0],
            ['private', 'registered', 'public'],
        );
        $I->seeResponseJsonMatchesJsonPath('$.results[0].archived');
        // Caller context is not part of this shape - it is the same for everyone asking.
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].isMember');
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].unreadCount');
    }

    public function testScopesToTheCallersOwnSpaces(ApiTester $I)
    {
        $I->wantTo('list my own spaces');
        // User 1 is a member of 1, 3, 4 and 5. There is no follow fixture to reset, so the
        // test starts from a known state itself.
        Follow::deleteAll(['user_id' => 1, 'object_model' => Space::class]);
        $follow = new Follow([
            'user_id' => 1,
            'object_model' => Space::class,
            'object_id' => 2,
        ]);
        Assert::assertTrue($follow->save(), 'seeded a followed space');

        $I->amLoggedInAs(1);
        $I->sendGet('space', ['scope' => 'mine']);

        $I->seeResponseCodeIs(200);
        $names = $this->names($I);

        Assert::assertContains('Space 1', $names, 'a membership is mine');
        Assert::assertContains('Space 2', $names, 'a followed space is mine');
        // Memberships come first, the followed space after them - the order the space menu has.
        Assert::assertSame('Space 2', end($names));
    }

    public function testScopesToMembershipsOnly(ApiTester $I)
    {
        $I->wantTo('list only the spaces I am a member of');
        $I->amLoggedInAs(4);
        $I->sendGet('space', ['scope' => 'member']);

        $I->seeResponseCodeIs(200);
        Assert::assertSame([], $this->names($I), 'user 4 is a member of nothing');
    }

    public function testLeavesArchivedSpacesOutUnlessAsked(ApiTester $I)
    {
        $I->wantTo('not stumble over archived spaces');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $I->amLoggedInAs(1);
        $I->sendGet('space');
        $I->seeResponseCodeIs(200);
        Assert::assertNotContains('Space 3', $this->names($I));

        $I->sendGet('space', ['archived' => 1]);
        $I->seeResponseCodeIs(200);
        Assert::assertContains('Space 3', $this->names($I));
    }

    public function testAnswersTheCallersStateForTheNamedSpaces(ApiTester $I)
    {
        $I->wantTo('learn what I am to the spaces I am shown');
        // There is no follow fixture, so follows another test seeded are still around - this
        // test is about a membership and says so explicitly.
        Follow::deleteAll(['user_id' => 1, 'object_model' => Space::class]);
        // Everything in the fixture predates this, so a membership that has never been
        // visited counts nothing, and one visited long ago counts what was posted since.
        // Space 1 is the one with content in the fixture (posts 7-9), and user 1 is a member.
        Membership::updateAll(['last_visit' => '2010-01-01 00:00:00'], ['user_id' => 1, 'space_id' => 1]);

        $I->amLoggedInAs(1);
        $I->sendGet('space/states', ['ids' => [1, 2]]);

        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];

        Assert::assertTrue($states[1]['isMember'], 'space 1 is one of mine');
        Assert::assertGreaterThan(
            0,
            $states[1]['newItems'],
            'and it counts what was posted since my last visit',
        );

        // A space the caller has no relation to still answers - a client showing it should
        // learn "nothing of mine" rather than have to tell that from a missing key.
        Assert::assertFalse($states[2]['isMember']);
        Assert::assertFalse($states[2]['isFollowing']);
        Assert::assertSame(0, $states[2]['newItems']);
    }

    public function testTellsFollowingApartFromMembership(ApiTester $I)
    {
        $I->wantTo('see which of my spaces I only follow');
        Follow::deleteAll(['user_id' => 1, 'object_model' => Space::class]);
        $follow = new Follow(['user_id' => 1, 'object_model' => Space::class, 'object_id' => 2]);
        Assert::assertTrue($follow->save());

        $I->amLoggedInAs(1);
        $I->sendGet('space/states', ['ids' => [2]]);

        $I->seeResponseCodeIs(200);
        $state = json_decode($I->grabResponse(), true)['results'][2];

        Assert::assertFalse($state['isMember'], 'user 1 is not a member of space 2');
        Assert::assertTrue($state['isFollowing'], 'but follows it');
    }

    public function testAnswersAnEmptyMapWithoutIds(ApiTester $I)
    {
        $I->wantTo('ask for no counts at all');
        $I->amLoggedInAs(1);
        $I->sendGet('space/states');

        $I->seeResponseCodeIs(200);
        Assert::assertSame([], json_decode($I->grabResponse(), true)['results']);
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->wantTo('be rejected without a session');
        $I->sendGet('space');
        $I->seeResponseCodeIs(401);

        $I->sendGet('space/states');
        $I->seeResponseCodeIs(401);
    }

    public function testStatesAcceptCommaSeparatedIds(ApiTester $I)
    {
        $I->wantTo('ask for space states with a comma-separated id list');
        $I->amLoggedInAs(1);

        $I->sendGet('space/states?ids=1,2');
        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];
        Assert::assertSame(['1', '2'], array_map('strval', array_keys($states)));

        $I->sendGet('space/states', ['ids' => [1, 2]]);
        Assert::assertSame($states, json_decode($I->grabResponse(), true)['results'], 'both spellings answer the same');
    }

    private function ids(ApiTester $I): array
    {
        return array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testFiltersByScopeNone(ApiTester $I)
    {
        $I->wantTo('list the spaces I am neither a member nor a follower of');
        Follow::deleteAll(['user_id' => 4, 'object_model' => Space::class]);
        $follow = new Follow(['user_id' => 4, 'object_model' => Space::class, 'object_id' => 2]);
        Assert::assertTrue($follow->save());

        $I->amLoggedInAs(4);
        $I->sendGet('space', ['scope' => 'none']);

        $I->seeResponseCodeIs(200);
        $ids = $this->ids($I);
        sort($ids);
        Assert::assertSame([1, 3, 4], $ids, 'the followed space 2 and the invisible space 5 are not listed');
    }

    public function testSortsByTheRequestedOrder(ApiTester $I)
    {
        $I->wantTo('sort the space list');
        foreach ([1 => ['Delta', '2020-01-03'], 2 => ['Alpha', '2020-01-01'], 3 => ['Charlie', '2020-01-04'], 4 => ['Bravo', '2020-01-02']] as $id => [$name, $createdAt]) {
            Space::updateAll(['name' => $name, 'created_at' => $createdAt], ['id' => $id]);
        }

        $I->amLoggedInAs(4);
        $I->sendGet('space', ['sort' => 'name']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([2, 4, 3, 1], $this->ids($I));

        $I->sendGet('space', ['sort' => 'newest']);
        Assert::assertSame([3, 1, 4, 2], $this->ids($I));

        $I->sendGet('space', ['sort' => 'oldest']);
        Assert::assertSame([2, 4, 1, 3], $this->ids($I));
    }

    public function testNarrowsToIdsAndExcludes(ApiTester $I)
    {
        $I->wantTo('ask for named spaces only, or leave some out');
        $I->amLoggedInAs(4);

        $I->sendGet('space?ids=2,3,5');
        $I->seeResponseCodeIs(200);
        $ids = $this->ids($I);
        sort($ids);
        Assert::assertSame([2, 3], $ids, 'ids never widen visibility');

        $I->sendGet('space', ['ids' => [2, 3], 'exclude' => [3]]);
        Assert::assertSame([2], $this->ids($I));

        $I->sendGet('space?exclude=1,2');
        $ids = $this->ids($I);
        sort($ids);
        Assert::assertSame([3, 4], $ids);
    }

    public function testListsOnlyArchivedSpacesWhenAsked(ApiTester $I)
    {
        $I->wantTo('list the archived spaces');
        Space::updateAll(['status' => Space::STATUS_ARCHIVED], ['id' => 3]);

        $I->amLoggedInAs(4);
        $I->sendGet('space', ['archived' => 1]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame([3], $this->ids($I));

        $I->sendGet('space', ['archived' => 0]);
        Assert::assertNotContains(3, $this->ids($I));
    }

    public function testRejectsUnknownValues(ApiTester $I)
    {
        $I->wantTo('be told about a parameter value the list does not know');
        $I->amLoggedInAs(4);

        foreach ([
            'scope' => ['scope' => 'friends'],
            'sort' => ['sort' => 'random'],
            'purpose' => ['purpose' => 'stream'],
            'archived' => ['archived' => 'yes'],
            'ids' => ['ids' => 'abc'],
            'exclude' => ['exclude' => '1,x'],
        ] as $name => $params) {
            $I->sendGet('space', $params);
            $I->seeResponseCodeIs(422);
            $I->seeResponseJsonMatchesJsonPath('$.errors.' . $name);
        }
    }

    public function testPassesUnknownParametersAndThePurposeToTheEvent(ApiTester $I)
    {
        $I->wantTo('let a module restrict the list by its own parameter and the purpose');
        $received = null;
        $handler = function (SpaceListQueryEvent $event) use (&$received) {
            $received = ['purpose' => $event->purpose, 'category' => $event->params['category'] ?? null];
            if ($event->purpose === SpaceListQuery::PURPOSE_DIRECTORY && ($event->params['category'] ?? null) === 'two') {
                $event->query->andWhere(['space.id' => 2]);
            }
        };
        Event::on(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);

        try {
            $I->amLoggedInAs(4);
            $I->sendGet('space', ['category' => 'two', 'purpose' => 'directory']);
            $I->seeResponseCodeIs(200);
            Assert::assertSame(['purpose' => 'directory', 'category' => 'two'], $received);
            Assert::assertSame([2], $this->ids($I), 'the handler restricted the list');

            $I->sendGet('space', ['category' => 'two']);
            $I->seeResponseCodeIs(200);
            Assert::assertNull($received['purpose'], 'absent purpose is neutral');
            Assert::assertGreaterThan(1, count($this->ids($I)));
        } finally {
            Event::off(SpaceListQuery::class, SpaceListQuery::EVENT_INIT, $handler);
        }
    }

    public function testChooserPurposeKeepsItsList(ApiTester $I)
    {
        $I->wantTo('see the chooser request answer as before');
        Follow::deleteAll(['user_id' => 1, 'object_model' => Space::class]);

        $I->amLoggedInAs(1);
        $I->sendGet('space', ['scope' => 'mine']);
        $withoutPurpose = $this->ids($I);

        $I->sendGet('space', ['scope' => 'mine', 'purpose' => 'chooser', 'q' => '']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame($withoutPurpose, $this->ids($I));
        Assert::assertSame([1, 3, 4, 5], $this->ids($I), 'the memberships of user 1');
    }

    public function testCarriesMemberAndFollowerCounts(ApiTester $I)
    {
        $I->wantTo('see how many members and followers a listed space has');
        Follow::deleteAll(['object_model' => Space::class]);
        foreach ([1, 4] as $userId) {
            $follow = new Follow(['user_id' => $userId, 'object_model' => Space::class, 'object_id' => 2]);
            Assert::assertTrue($follow->save());
        }
        Space::findOne(['id' => 4])->settings->set('hideMembers', true);
        Space::findOne(['id' => 3])->settings->set('hideFollowers', true);

        $I->amLoggedInAs(4);
        $I->sendGet('space', ['ids' => [2, 3, 4]]);
        $I->seeResponseCodeIs(200);

        $byId = array_column(json_decode($I->grabResponse(), true)['results'], null, 'id');
        Assert::assertSame(1, $byId[2]['memberCount'], 'space 2 has one member');
        Assert::assertSame(2, $byId[2]['followerCount'], 'and two followers');
        Assert::assertSame(3, $byId[3]['memberCount']);
        Assert::assertNull($byId[3]['followerCount'], 'a space hiding its followers does not tell their number');
        Assert::assertNull($byId[4]['memberCount'], 'a space hiding its members does not tell their number');
        Assert::assertSame(0, $byId[4]['followerCount']);

        Space::findOne(['id' => 4])->settings->delete('hideMembers');
        Space::findOne(['id' => 3])->settings->delete('hideFollowers');
    }

    public function testCarriesTheBannerUrl(ApiTester $I)
    {
        $I->wantTo('see the banner of a listed space');
        $space = Space::findOne(['id' => 2]);
        $space->getBannerImage()->setByFile(Yii::getAlias('@humhub/resources/img/default_banner.jpg'));

        try {
            $I->amLoggedInAs(4);
            $I->sendGet('space', ['ids' => [2, 3]]);
            $I->seeResponseCodeIs(200);

            $byId = array_column(json_decode($I->grabResponse(), true)['results'], null, 'id');
            Assert::assertStringStartsWith('http', $byId[2]['bannerUrl'], 'an absolute URL');
            Assert::assertNull($byId[3]['bannerUrl'], 'no banner of its own');
        } finally {
            Space::findOne(['id' => 2])->getBannerImage()->delete();
        }
    }

    public function testCapsThePageSize(ApiTester $I)
    {
        $I->wantTo('not get more than 100 spaces per page');
        $I->amLoggedInAs(4);
        $I->sendGet('space', ['pageSize' => 1000]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(100, $I->grabDataFromResponseByJsonPath('$.pageSize')[0]);

        $I->sendGet('space');
        Assert::assertSame(25, $I->grabDataFromResponseByJsonPath('$.pageSize')[0]);
    }

    public function testStatesCarryMembershipAndWhatTheCallerMayDo(ApiTester $I)
    {
        $I->wantTo('learn per space my membership, whether I may follow and what I may see');
        Follow::deleteAll(['object_model' => Space::class]);
        $follow = new Follow(['user_id' => 3, 'object_model' => Space::class, 'object_id' => 2]);
        Assert::assertTrue($follow->save());
        // User1 (2) is not a member of space 1; it hides its members from non-privileged users.
        Space::findOne(['id' => 1])->settings->set('hideMembers', true);
        Space::findOne(['id' => 3])->settings->set('hideFollowers', true);

        try {
            $I->amLoggedInAs(2);
            $I->sendGet('space/states', ['ids' => [1, 2, 3]]);
            $I->seeResponseCodeIs(200);
            $states = json_decode($I->grabResponse(), true)['results'];

            // Space 2: User1 owns it.
            Assert::assertSame('member', $states[2]['membership']['state']);
            Assert::assertTrue($states[2]['membership']['isOwner']);
            Assert::assertFalse($states[2]['membership']['canLeave'], 'an owner cannot leave');
            Assert::assertFalse($states[2]['canFollow'], 'members cannot follow');
            Assert::assertTrue($states[2]['canViewMembers']);
            Assert::assertTrue($states[2]['canViewFollowers']);
            Assert::assertSame(1, $states[2]['memberCount']);
            Assert::assertSame(1, $states[2]['followerCount'], 'User2 follows it');

            // Space 1: not a member, membership needs approval.
            Assert::assertSame('none', $states[1]['membership']['state']);
            Assert::assertTrue($states[1]['membership']['canJoin']);
            Assert::assertTrue($states[1]['membership']['needsApproval']);
            Assert::assertFalse($states[1]['membership']['isFollowing']);
            Assert::assertTrue($states[1]['canFollow']);
            Assert::assertFalse($states[1]['canViewMembers'], 'the space hides its members');
            Assert::assertNull($states[1]['memberCount']);

            // Space 3: a member, the space hides its followers.
            Assert::assertSame('member', $states[3]['membership']['state']);
            Assert::assertFalse($states[3]['canViewFollowers']);
            Assert::assertNull($states[3]['followerCount']);

            // The membership shape is the one `space/<id>/membership` answers.
            $I->sendGet('space/1/membership');
            Assert::assertSame(json_decode($I->grabResponse(), true), $states[1]['membership']);
        } finally {
            Space::findOne(['id' => 1])->settings->delete('hideMembers');
            Space::findOne(['id' => 3])->settings->delete('hideFollowers');
        }
    }

    public function testStatesTellWhenFollowingIsDisabled(ApiTester $I)
    {
        $I->wantTo('not be offered following while it is disabled');
        $module = Yii::$app->getModule('space');
        $module->disableFollow = true;

        try {
            $I->amLoggedInAs(4);
            $I->sendGet('space/states', ['ids' => [2]]);
            $I->seeResponseCodeIs(200);
            $state = json_decode($I->grabResponse(), true)['results'][2];
            Assert::assertFalse($state['canFollow']);
            Assert::assertFalse($state['canViewFollowers']);
            Assert::assertNull($state['followerCount']);
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testCapsTheIdLists(ApiTester $I)
    {
        $I->wantTo('be refused naming more than 100 spaces');
        $I->amLoggedInAs(4);

        $I->sendGet('space', ['ids' => implode(',', range(1, 101))]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.ids');

        $I->sendGet('space', ['exclude' => range(1, 101)]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.exclude');

        $I->sendGet('space', ['ids' => implode(',', range(1, 100))]);
        $I->seeResponseCodeIs(200);
    }
}
