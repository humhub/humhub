<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\friendship\notifications\FriendshipRequestNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\user\notifications\FollowedNotification;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The notification API (`humhub\modules\notification\controllers\api\NotificationController`),
 * consumed by the notification islands.
 *
 * The notification fixture set is empty, so every test seeds the notifications it needs
 * in-process — which also keeps them independent of the ordering rules of a shared baseline.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class NotificationApiCest
{
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    /**
     * Seeds a listed notification for the given user - a row of a real notification class
     * without a source, each its own entry.
     *
     * @return int the notification record id
     */
    private function seedNotification(int $userId, string $class = FollowedNotification::class, int $originatorId = 2, bool $seen = false): int
    {
        $record = new Notification([
            'class' => $class,
            'user_id' => $userId,
            'originator_id' => $originatorId,
            'priority' => $class::priority()->value,
            'listed' => 1,
            'seen_at' => $seen ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        Assert::assertTrue($record->save());

        return (int)$record->id;
    }

    /**
     * @return int[]
     */
    private function grabIds(ApiTester $I): array
    {
        return array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testListsTheCallersNotifications(ApiTester $I)
    {
        $I->wantTo('read my own notifications');
        $mine = $this->seedNotification(1);
        $foreign = $this->seedNotification(2, originatorId: 3);

        $I->amLoggedInAs(1);
        $I->sendGet('notification?limit=50');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $ids = $this->grabIds($I);
        Assert::assertContains($mine, $ids);
        Assert::assertNotContains($foreign, $ids, 'someone else\'s notification is not in my list');

        $entry = $I->grabDataFromResponseByJsonPath('$.results[?(@.id == ' . $mine . ')]')[0];
        Assert::assertSame(
            ['id', 'html', 'url', 'isNew', 'createdAt', 'groupKey', 'count', 'priority', 'originator', 'space'],
            array_keys($entry),
        );
        Assert::assertStringContainsString('following you', $entry['html']);
        Assert::assertTrue($entry['isNew']);
        Assert::assertSame(NotificationListService::encodeCursor($mine), $entry['groupKey']);
        Assert::assertSame(1, $entry['count']);
        Assert::assertSame('low', $entry['priority']);
        Assert::assertSame(2, $entry['originator']['id']);
        Assert::assertGreaterThan(0, (int)$I->grabDataFromResponseByJsonPath('$.unseenCount')[0]);
    }

    public function testPagesWithACursorAndStopsWhenExhausted(ApiTester $I)
    {
        $I->wantTo('page through my notifications with a cursor');
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->seedNotification(1);
        }

        $I->amLoggedInAs(1);

        $I->sendGet('notification?limit=2');
        $I->seeResponseCodeIs(200);
        $firstPage = $this->grabIds($I);
        Assert::assertSame([$ids[2], $ids[1]], $firstPage, 'newest entry first');
        $cursor = $I->grabDataFromResponseByJsonPath('$.nextCursor')[0];
        Assert::assertIsString($cursor, 'the cursor is opaque');
        Assert::assertSame(
            $I->grabDataFromResponseByJsonPath('$.results[1].groupKey')[0],
            $cursor,
            'the cursor is the key of the last entry of the page',
        );

        $I->sendGet('notification?limit=2&cursor=' . urlencode($cursor));
        $I->seeResponseCodeIs(200);
        $secondPage = $this->grabIds($I);
        Assert::assertSame($ids[0], $secondPage[0], 'the next page continues behind the cursor');
        Assert::assertEmpty(array_intersect($firstPage, $secondPage), 'no entry appears on both pages');

        // The last page is short, so there is nothing behind it.
        $I->sendGet('notification?limit=50&cursor=' . urlencode($cursor));
        $I->seeResponseCodeIs(200);
        Assert::assertNull($I->grabDataFromResponseByJsonPath('$.nextCursor')[0]);
    }

    public function testClampsTheRequestedLimit(ApiTester $I)
    {
        $I->wantTo('not be able to ask for an unbounded page');
        for ($i = 0; $i < 4; $i++) {
            $this->seedNotification(1);
        }

        $I->amLoggedInAs(1);

        $I->sendGet('notification?limit=1000');
        $I->seeResponseCodeIs(200);
        Assert::assertLessThanOrEqual(50, count($this->grabIds($I)));

        $I->sendGet('notification?limit=0');
        $I->seeResponseCodeIs(200);
        Assert::assertCount(1, $this->grabIds($I));
    }

    public function testFiltersBySeenState(ApiTester $I)
    {
        $I->wantTo('filter my notifications by their seen state');
        $unseen = $this->seedNotification(1);
        $seen = $this->seedNotification(1, seen: true);

        $I->amLoggedInAs(1);

        $I->sendGet('notification?seen=unseen&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = $this->grabIds($I);
        Assert::assertContains($unseen, $ids);
        Assert::assertNotContains($seen, $ids);

        $I->sendGet('notification?seen=seen&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = $this->grabIds($I);
        Assert::assertContains($seen, $ids);
        Assert::assertNotContains($unseen, $ids);

        $I->sendGet('notification?seen=sometimes');
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.seen');

        $I->sendGet('notification?seen[]=seen');
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.seen');

        // an array is no cursor
        $I->sendGet('notification?cursor[]=x&limit=50');
        $I->seeResponseCodeIs(200);
        Assert::assertContains($unseen, $this->grabIds($I));
    }

    public function testFiltersByGroup(ApiTester $I)
    {
        $I->wantTo('filter my notifications by notification group');
        // Real classes, since a group filter resolves to the notification classes the modules
        // currently register.
        $social = $this->seedNotification(1, FollowedNotification::class);
        $direct = $this->seedNotification(1, FriendshipRequestNotification::class);

        $I->amLoggedInAs(1);

        $I->sendGet('notification?groups[]=social&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = $this->grabIds($I);
        Assert::assertContains($social, $ids);
        Assert::assertNotContains($direct, $ids);

        $I->sendGet('notification?groups[]=social&groups[]=direct&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = $this->grabIds($I);
        Assert::assertContains($social, $ids);
        Assert::assertContains($direct, $ids);

        // A group nothing belongs to leaves the list empty rather than unfiltered.
        $I->sendGet('notification?groups[]=there-is-no-such-group&limit=50');
        $I->seeResponseCodeIs(200);
        Assert::assertEmpty($this->grabIds($I));

        // So does an empty selection.
        $I->sendGet('notification?groups[]=&limit=50');
        $I->seeResponseCodeIs(200);
        Assert::assertEmpty($this->grabIds($I));
    }

    public function testDropsAnInconsistentNotificationInsteadOfFailing(ApiTester $I)
    {
        $I->wantTo('get a usable list even when one notification is broken');
        $good = $this->seedNotification(1);
        $broken = $this->seedNotification(1);
        // A class that no longer exists is what an uninstalled module leaves behind.
        Notification::updateAll(['class' => 'humhub\\modules\\gone\\notifications\\GoneNotification'], ['id' => $broken]);

        $I->amLoggedInAs(1);
        $I->sendGet('notification?limit=50');

        $I->seeResponseCodeIs(200);
        $ids = $this->grabIds($I);
        Assert::assertContains($good, $ids);
        Assert::assertNotContains($broken, $ids);
        Assert::assertNull(Notification::findOne(['id' => $broken]), 'the broken notification is deleted');
    }

    public function testMarkAsSeenClearsTheUnseenCount(ApiTester $I)
    {
        $I->wantTo('mark all my notifications as seen');
        $this->seedNotification(1);
        $foreign = $this->seedNotification(2, originatorId: 3);

        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['unseenCount' => 0]);

        $I->sendGet('notification?limit=50');
        Assert::assertSame(0, (int)$I->grabDataFromResponseByJsonPath('$.unseenCount')[0]);
        Assert::assertNull(Notification::findOne(['id' => $foreign])->seen_at, 'only my own were touched');
    }

    public function testMarkAsSeenByIds(ApiTester $I)
    {
        $I->wantTo('mark single entries as seen');
        $first = $this->seedNotification(1);
        $second = $this->seedNotification(1);
        $foreign = $this->seedNotification(2, originatorId: 3);

        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendGet('notification?limit=50');
        $before = (int)$I->grabDataFromResponseByJsonPath('$.unseenCount')[0];

        $I->sendPost('notification/mark-as-seen', ['ids' => [$first, $foreign]]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['unseenCount' => $before - 1]);

        Assert::assertNotNull(Notification::findOne(['id' => $first])->seen_at);
        Assert::assertNull(Notification::findOne(['id' => $second])->seen_at, 'only the given entry');
        Assert::assertNull(Notification::findOne(['id' => $foreign])->seen_at, 'a foreign id is ignored');
    }

    public function testMarkAsSeenNeedsACsrfTokenAndThePostVerb(ApiTester $I)
    {
        $I->wantTo('be refused without a CSRF token or with the wrong verb');
        $this->seedNotification(1);

        $I->amLoggedInAs(1);

        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(403);

        $I->sendGet('notification/mark-as-seen');
        $I->seeResponseCodeIs(404);
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendGet('notification');
        $I->seeResponseCodeIs(401);

        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(401);
    }
}
