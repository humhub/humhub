<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The follow relationship of the caller to a user
 * (`humhub\modules\user\controllers\api\FollowController`).
 *
 * Fixture ground truth: User1 (2) is followed by Admin (1) — the fixture's one user follow,
 * cleared before every test so each starts from no follows. User3 (4) is the caller of most
 * tests; DisabledUser (5) is disabled.
 *
 * One identity per test, as in {@see CommentApiCest}.
 */
class UserFollowApiCest
{
    private const USER1 = 2;

    private const USER3 = 4;

    private const DISABLED = 5;

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    private function state(ApiTester $I): array
    {
        return json_decode($I->grabResponse(), true);
    }

    private function follow(int $userId): ?Follow
    {
        return Follow::findOne(['user_id' => $userId, 'object_model' => User::class, 'object_id' => self::USER1]);
    }

    public function _before(): void
    {
        Follow::deleteAll(['object_model' => User::class]);
        ContentContainerBlockedUsers::deleteAll(['user_id' => self::USER3]);
    }

    public function _after(): void
    {
        // Settings outlive the test's transaction in the cache the requests read them from.
        Yii::$app->getModule('user')->settings->delete('auth.blockUsers');
    }

    public function testReadsTheStateOfANonFollower(ApiTester $I)
    {
        $I->wantTo('read my follow state of a user I do not follow');
        $I->amLoggedInAs(self::USER3);
        $I->sendGet('user/' . self::USER1 . '/follow');

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => false, 'followerCount' => 0, 'canFollow' => true], $this->state($I));
    }

    public function testFollowsAndUnfollowsIdempotently(ApiTester $I)
    {
        $I->wantTo('follow a user and stop following them again');
        $I->amLoggedInAs(self::USER3);
        $this->withCsrf($I);

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => true, 'followerCount' => 1, 'canFollow' => true], $this->state($I));
        Assert::assertSame(1, (int)$this->follow(self::USER3)->send_notifications, 'followed with notifications, like the profile button did');

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertTrue($this->state($I)['isFollowing'], 'following twice is a success');
        Assert::assertSame(1, (int)Follow::find()->where(['user_id' => self::USER3, 'object_model' => User::class])->count());

        $I->sendDelete('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => false, 'followerCount' => 0, 'canFollow' => true], $this->state($I));
        Assert::assertNull($this->follow(self::USER3));

        $I->sendDelete('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertFalse($this->state($I)['isFollowing'], 'unfollowing twice is a success');
    }

    public function testThereIsNoFollowOfOneself(ApiTester $I)
    {
        $I->wantTo('be refused following myself');
        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendGet('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(403);

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->follow(self::USER1));
    }

    public function testDisabledFollowingRefusesFollowButLetsAFollowEnd(ApiTester $I)
    {
        $I->wantTo('not follow while following is disabled, but end an existing follow');
        $follow = new Follow(['user_id' => self::USER3, 'object_model' => User::class, 'object_id' => self::USER1]);
        Assert::assertTrue($follow->save());
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;

        try {
            $I->amLoggedInAs(self::USER3);
            $this->withCsrf($I);

            $I->sendGet('user/' . self::USER1 . '/follow');
            $I->seeResponseCodeIs(200);
            Assert::assertSame(['isFollowing' => true, 'followerCount' => null, 'canFollow' => false], $this->state($I));

            $I->sendDelete('user/' . self::USER1 . '/follow');
            $I->seeResponseCodeIs(200);
            Assert::assertFalse($this->state($I)['isFollowing']);

            $I->sendPut('user/' . self::USER1 . '/follow');
            $I->seeResponseCodeIs(403);
            Assert::assertNull($this->follow(self::USER3));
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testUnknownAndUnavailableUsers(ApiTester $I)
    {
        $I->wantTo('get nothing for a user who does not exist, is disabled or blocked me');
        Yii::$app->getModule('user')->settings->set('auth.blockUsers', true);
        Yii::$app->db->createCommand()->insert(ContentContainerBlockedUsers::tableName(), [
            'contentcontainer_id' => User::findOne(['id' => self::USER1])->contentcontainer_id,
            'user_id' => self::USER3,
        ])->execute();

        $I->amLoggedInAs(self::USER3);
        $this->withCsrf($I);

        $I->sendGet('user/99999/follow');
        $I->seeResponseCodeIs(404);

        $I->sendGet('user/' . self::DISABLED . '/follow');
        $I->seeResponseCodeIs(403);

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(403, 'User1 blocked me');
        Assert::assertSame(0, (int)Follow::find()->where(['user_id' => self::USER3, 'object_model' => User::class])->count());
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendGet('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(401);

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(401);
    }

    public function testAStateChangingRequestNeedsACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused without a CSRF token');
        $I->amLoggedInAs(self::USER3);

        $I->sendPut('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->follow(self::USER3));

        $I->sendDelete('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(403);
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->wantTo('find no POST route on the follow endpoint - it is set with PUT');
        $I->amLoggedInAs(self::USER3);
        $this->withCsrf($I);

        $I->sendPost('user/' . self::USER1 . '/follow');
        $I->seeResponseCodeIs(404);
        Assert::assertNull($this->follow(self::USER3));
    }

    public function testTheWebFollowActionsAreGone(ApiTester $I)
    {
        $I->wantTo('find the old web follow actions removed - there is one way per transition');
        $guid = User::findOne(['id' => self::USER1])->guid;
        $I->amLoggedInAs(self::USER3);
        $this->withCsrf($I);

        // The same route shape reaches the controller's remaining actions ...
        $I->sendGet('http://localhost:8080/user/profile/follower-list?cguid=' . $guid);
        $I->seeResponseCodeIs(200);

        // ... but no longer a follow or unfollow.
        $I->sendPost('http://localhost:8080/user/profile/follow?cguid=' . $guid);
        $I->seeResponseCodeIs(404);
        Assert::assertNull($this->follow(self::USER3));

        $I->sendPost('http://localhost:8080/user/profile/unfollow?cguid=' . $guid);
        $I->seeResponseCodeIs(404);
    }
}
