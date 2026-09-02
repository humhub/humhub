<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use PHPUnit\Framework\Assert;

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
        $I->seeResponseJsonMatchesJsonPath('$.results[0].visibility');
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
        $I->sendGet('space/states', ['guids' => [$this->guid(1), $this->guid(2)]]);

        $I->seeResponseCodeIs(200);
        $states = json_decode($I->grabResponse(), true)['results'];

        Assert::assertTrue($states[$this->guid(1)]['isMember'], 'space 1 is one of mine');
        Assert::assertGreaterThan(
            0,
            $states[$this->guid(1)]['newItems'],
            'and it counts what was posted since my last visit',
        );

        // A space the caller has no relation to still answers - a client showing it should
        // learn "nothing of mine" rather than have to tell that from a missing key.
        Assert::assertFalse($states[$this->guid(2)]['isMember']);
        Assert::assertFalse($states[$this->guid(2)]['isFollowing']);
        Assert::assertSame(0, $states[$this->guid(2)]['newItems']);
    }

    public function testTellsFollowingApartFromMembership(ApiTester $I)
    {
        $I->wantTo('see which of my spaces I only follow');
        Follow::deleteAll(['user_id' => 1, 'object_model' => Space::class]);
        $follow = new Follow(['user_id' => 1, 'object_model' => Space::class, 'object_id' => 2]);
        Assert::assertTrue($follow->save());

        $I->amLoggedInAs(1);
        $I->sendGet('space/states', ['guids' => [$this->guid(2)]]);

        $I->seeResponseCodeIs(200);
        $state = json_decode($I->grabResponse(), true)['results'][$this->guid(2)];

        Assert::assertFalse($state['isMember'], 'user 1 is not a member of space 2');
        Assert::assertTrue($state['isFollowing'], 'but follows it');
    }

    public function testAnswersAnEmptyMapWithoutGuids(ApiTester $I)
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
}
