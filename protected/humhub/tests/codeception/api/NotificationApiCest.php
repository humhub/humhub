<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\notification\models\Notification;
use humhub\modules\post\models\Post;
use humhub\modules\user\notifications\Followed;
use humhub\modules\user\models\User;
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
     * Seeds a web notification for the given user, the same way the platform does (through the
     * notification class, so the record carries a resolvable base model).
     *
     * @return int the notification record id
     */
    private function seedNotification(int $userId, int $postId = 1, int $originatorId = 2, bool $seen = false): int
    {
        $notification = \humhub\modules\notification\tests\codeception\unit\rendering\notifications\TestNotification::instance()
            ->from(User::findOne(['id' => $originatorId]))
            ->about(Post::findOne(['id' => $postId]));
        $notification->saveRecord(User::findOne(['id' => $userId]));

        $record = $notification->record;
        $record->updateAttributes([
            'send_web_notifications' => 1,
            'seen' => $seen ? 1 : 0,
        ]);

        return (int)$record->id;
    }

    public function testListsTheCallersNotifications(ApiTester $I)
    {
        $I->wantTo('read my own notifications');
        $mine = $this->seedNotification(1);
        $foreign = $this->seedNotification(2);

        $I->amLoggedInAs(1);
        $I->sendGet('notification');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($mine, $ids);
        Assert::assertNotContains($foreign, $ids, 'someone else\'s notification is not in my list');

        $I->seeResponseJsonMatchesJsonPath('$.results[0].html');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].url');
        $I->seeResponseJsonMatchesJsonPath('$.results[0].createdAt');
        $I->seeResponseJsonMatchesJsonPath('$.unseenCount');
    }

    public function testPagesWithACursorAndStopsWhenExhausted(ApiTester $I)
    {
        $I->wantTo('page through my notifications with a cursor');
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->seedNotification(1);
        }
        rsort($ids);

        $I->amLoggedInAs(1);

        $I->sendGet('notification?limit=2');
        $I->seeResponseCodeIs(200);
        $firstPage = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertCount(2, $firstPage);
        $cursor = (int)$I->grabDataFromResponseByJsonPath('$.nextCursor')[0];
        Assert::assertSame(end($firstPage), $cursor, 'the cursor is the last entry of the page');

        $I->sendGet("notification?limit=2&cursor=$cursor");
        $I->seeResponseCodeIs(200);
        $secondPage = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertEmpty(array_intersect($firstPage, $secondPage), 'no entry appears on both pages');

        // The last page is short, so there is nothing behind it.
        $I->sendGet('notification?limit=50');
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
        Assert::assertLessThanOrEqual(50, count($I->grabDataFromResponseByJsonPath('$.results[*].id')));

        $I->sendGet('notification?limit=0');
        $I->seeResponseCodeIs(200);
        Assert::assertCount(1, $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testFiltersBySeenState(ApiTester $I)
    {
        $I->wantTo('filter my notifications by their seen state');
        $unseen = $this->seedNotification(1);
        $seen = $this->seedNotification(1, 2, 2, true);

        $I->amLoggedInAs(1);

        $I->sendGet('notification?seen=unseen&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($unseen, $ids);
        Assert::assertNotContains($seen, $ids);

        $I->sendGet('notification?seen=seen&limit=50');
        $I->seeResponseCodeIs(200);
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($seen, $ids);
        Assert::assertNotContains($unseen, $ids);
    }

    public function testFiltersByCategory(ApiTester $I)
    {
        $I->wantTo('filter my notifications by category');
        // A notification of a REAL category, since a category filter resolves to the
        // notification classes the modules currently register.
        $followed = Followed::instance()
            ->from(User::findOne(['id' => 2]))
            ->about(User::findOne(['id' => 1]));
        $followed->saveRecord(User::findOne(['id' => 1]));
        $followed->record->updateAttributes(['send_web_notifications' => 1]);
        $followedId = (int)$followed->record->id;

        $I->amLoggedInAs(1);

        $I->sendGet('notification?categories[]=followed&limit=50');
        $I->seeResponseCodeIs(200);
        Assert::assertContains($followedId, array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id')));

        // A category nothing belongs to leaves the list empty rather than unfiltered.
        $I->sendGet('notification?categories[]=there-is-no-such-category&limit=50');
        $I->seeResponseCodeIs(200);
        Assert::assertEmpty($I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testDropsAnInconsistentNotificationInsteadOfFailing(ApiTester $I)
    {
        $I->wantTo('get a usable list even when one notification is broken');
        $good = $this->seedNotification(1);
        $broken = $this->seedNotification(1);
        // A class that no longer exists is what an uninstalled module leaves behind.
        Notification::updateAll(['class' => 'humhub\\modules\\gone\\notifications\\Gone'], ['id' => $broken]);

        $I->amLoggedInAs(1);
        $I->sendGet('notification?limit=50');

        $I->seeResponseCodeIs(200);
        $ids = array_map('intval', $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertContains($good, $ids);
        Assert::assertNotContains($broken, $ids);
    }

    public function testMarkAsSeenClearsTheUnseenCount(ApiTester $I)
    {
        $I->wantTo('mark all my notifications as seen');
        $this->seedNotification(1);
        $foreign = $this->seedNotification(2);

        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['unseenCount' => 0]);

        $I->sendGet('notification?limit=50');
        Assert::assertSame(0, (int)$I->grabDataFromResponseByJsonPath('$.unseenCount')[0]);
        Assert::assertSame(0, (int)Notification::findOne(['id' => $foreign])->seen, 'only my own were touched');
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
