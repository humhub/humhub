<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\activity\tests\codeception\activities\TestContentGroupActivity;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;

/**
 * The activity API (`humhub\modules\activity\controllers\api\ActivityController`), consumed by
 * the `ActivityBox` island.
 *
 * The activity fixture set is empty, so every test seeds the activities it needs in-process.
 * They are always seeded for ANOTHER user than the caller: an activity of the caller's own is
 * filtered out by the query (`ActiveQueryActivity::excludeUser()`), which is what the box has
 * always done.
 *
 * Fixture ground truth used here: user 1 is a member of space 1 (with users 3), user 2 is the
 * only member of space 2; posts 7-9 live in space 1, posts 10-11 in space 2.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class ActivityApiCest
{
    /**
     * Seeds one activity of `$userId` on the given post, the way the platform does.
     *
     * @return int the id of the activity record
     */
    private function seedActivity(int $postId, int $userId): int
    {
        $activity = ActivityManager::dispatch(
            TestActivity::class,
            Post::findOne(['id' => $postId]),
            User::findOne(['id' => $userId]),
        );

        return (int)$activity->record->id;
    }

    public function testListsActivitiesOfSubscribedContainers(ApiTester $I)
    {
        $I->wantTo('read the activities of the containers I subscribe to');
        $mine = $this->seedActivity(9, 3);
        $foreign = $this->seedActivity(11, 2);

        $I->amLoggedInAs(1);
        $I->sendGet('activity');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($mine, $ids, 'an activity of a space I am a member of is listed');
        Assert::assertNotContains($foreign, $ids, 'an activity of a space I cannot see is not listed');

        $I->seeResponseJsonMatchesJsonPath('$.results[0].message');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].createdAt');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].groupCount');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].user.displayName');
    }

    public function testExcludesTheCallersOwnActivities(ApiTester $I)
    {
        $I->wantTo('not see my own activities');
        $own = $this->seedActivity(9, 1);
        $other = $this->seedActivity(8, 3);

        $I->amLoggedInAs(1);
        $I->sendGet('activity');

        $I->seeResponseCodeIs(200);
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertNotContains($own, $ids);
        Assert::assertContains($other, $ids);
    }

    public function testScopesToAContainer(ApiTester $I)
    {
        $I->wantTo('read the activities of one space only');
        $inSpace1 = $this->seedActivity(9, 3);
        // Post 2 lives on user 1's own profile - a different container, equally visible.
        $onProfile = $this->seedActivity(2, 3);

        $I->amLoggedInAs(1);
        $I->sendGet('activity', ['containerGuid' => Space::findOne(['id' => 1])->guid]);

        $I->seeResponseCodeIs(200);
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($inSpace1, $ids);
        Assert::assertNotContains($onProfile, $ids, 'another container\'s activity is not in a scoped list');
    }

    public function testAnswersNotFoundForAnUnknownContainer(ApiTester $I)
    {
        $I->wantTo('learn that the container I asked for does not exist');
        $I->amLoggedInAs(1);
        $I->sendGet('activity', ['containerGuid' => 'no-such-container']);

        $I->seeResponseCodeIs(404);
    }

    public function testPagesWithAnOpaqueCursor(ApiTester $I)
    {
        $I->wantTo('page through the activity list');
        $seeded = [
            $this->seedActivity(7, 3),
            $this->seedActivity(8, 3),
            $this->seedActivity(9, 3),
        ];

        $I->amLoggedInAs(1);
        $I->sendGet('activity', ['containerGuid' => Space::findOne(['id' => 1])->guid, 'limit' => 2]);

        $I->seeResponseCodeIs(200);
        $firstPage = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertCount(2, $firstPage);

        $cursor = $I->grabDataFromResponseByJsonPath('$.nextCursor')[0];
        Assert::assertNotEmpty($cursor, 'a full page carries a cursor for the next one');
        // The contract is an opaque token: a client passes it back and never builds one from an
        // entry - the grouping key behind it is not part of the payload (see ActivitySerializer).
        Assert::assertIsString($cursor);
        Assert::assertFalse(ctype_digit($cursor), 'the cursor is not an id a client could guess');

        $I->sendGet('activity', [
            'containerGuid' => Space::findOne(['id' => 1])->guid,
            'limit' => 2,
            'cursor' => $cursor,
        ]);

        $I->seeResponseCodeIs(200);
        $secondPage = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertNotEmpty($secondPage);
        Assert::assertEmpty(array_intersect($firstPage, $secondPage), 'pages do not overlap');
        Assert::assertEqualsCanonicalizing(
            $seeded,
            array_merge($firstPage, $secondPage),
            'both pages together are every seeded activity',
        );
        Assert::assertNull(
            $I->grabDataFromResponseByJsonPath('$.nextCursor')[0],
            'the last page ends the paging',
        );
    }

    public function testIgnoresAnUnreadableCursor(ApiTester $I)
    {
        $I->wantTo('receive the first page for a cursor I made up');
        $this->seedActivity(9, 3);

        $I->amLoggedInAs(1);
        $I->sendGet('activity', ['cursor' => 'not-a-cursor']);

        $I->seeResponseCodeIs(200);
        Assert::assertNotEmpty($I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testAGroupIsOneEntryAndReportsItsSize(ApiTester $I)
    {
        $I->wantTo('see a group of activities as a single entry');
        $post = Post::findOne(['id' => 9]);
        $user = User::findOne(['id' => 3]);

        // TestContentGroupActivity groups from its fourth activity on (groupingThreshold).
        for ($i = 0; $i < 4; $i++) {
            ActivityManager::dispatch(TestContentGroupActivity::class, $post, $user);
        }

        $I->amLoggedInAs(1);
        $I->sendGet('activity');

        $I->seeResponseCodeIs(200);
        $entries = $I->grabDataFromResponseByJsonPath('$.results[*].groupCount');
        Assert::assertSame([4], array_map('intval', $entries), 'four activities, one entry');

        $key = $I->grabDataFromResponseByJsonPath('$.results[0].key')[0];
        Assert::assertNotEmpty($key);
        Assert::assertFalse(ctype_digit($key), 'the entry key is opaque, not the internal id');
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->wantTo('be rejected without a session');
        $I->sendGet('activity');

        $I->seeResponseCodeIs(401);
    }
}
