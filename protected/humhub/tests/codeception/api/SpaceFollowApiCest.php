<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The follow relationship of the caller to a space
 * (`humhub\modules\space\controllers\api\FollowController`) — the endpoint the `FollowButton`
 * island talks to.
 *
 * Fixture ground truth: space 2 is public and its only member is User1 (2); space 5 is private
 * with the members Admin (1) and User1 (2). User3 (4) is a member of nothing. There is no follow
 * fixture, so every test starts by clearing the follows it relies on.
 *
 * One identity per test, as in {@see CommentApiCest}.
 */
class SpaceFollowApiCest
{
    private const SPACE_PUBLIC = 2;

    private const SPACE_PRIVATE = 5;

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
        return Follow::findOne(['user_id' => $userId, 'object_model' => Space::class, 'object_id' => self::SPACE_PUBLIC]);
    }

    public function _before(): void
    {
        Follow::deleteAll(['object_model' => Space::class]);
    }

    public function testReadsTheStateOfANonFollower(ApiTester $I)
    {
        $I->wantTo('read my follow state of a space I do not follow');
        $I->amLoggedInAs(4);
        $I->sendGet('space/' . self::SPACE_PUBLIC . '/follow');

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => false, 'followerCount' => 0, 'canFollow' => true], $this->state($I));
    }

    public function testFollowsAndUnfollowsIdempotently(ApiTester $I)
    {
        $I->wantTo('follow a space and stop following it again');
        $I->amLoggedInAs(4);
        $this->withCsrf($I);

        $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => true, 'followerCount' => 1, 'canFollow' => true], $this->state($I));
        Assert::assertSame(0, (int)$this->follow(4)->send_notifications, 'followed without notifications, like the web action did');

        $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertTrue($this->state($I)['isFollowing'], 'following twice is a success');
        Assert::assertSame(1, (int)Follow::find()->where(['user_id' => 4, 'object_model' => Space::class])->count());

        $I->sendDelete('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['isFollowing' => false, 'followerCount' => 0, 'canFollow' => true], $this->state($I));
        Assert::assertNull($this->follow(4));

        $I->sendDelete('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertFalse($this->state($I)['isFollowing'], 'unfollowing twice is a success');
    }

    public function testMembersCannotFollow(ApiTester $I)
    {
        $I->wantTo('be refused following a space I am a member of');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendGet('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(200);
        Assert::assertFalse($this->state($I)['canFollow']);

        $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->follow(2));
    }

    public function testDisabledFollowingRefusesFollowButLetsAFollowEnd(ApiTester $I)
    {
        $I->wantTo('not follow while following is disabled, but end an existing follow');
        $follow = new Follow(['user_id' => 4, 'object_model' => Space::class, 'object_id' => self::SPACE_PUBLIC]);
        Assert::assertTrue($follow->save());
        $module = Yii::$app->getModule('space');
        $module->disableFollow = true;

        try {
            $I->amLoggedInAs(4);
            $this->withCsrf($I);

            $I->sendGet('space/' . self::SPACE_PUBLIC . '/follow');
            $I->seeResponseCodeIs(200);
            $state = $this->state($I);
            Assert::assertTrue($state['isFollowing']);
            Assert::assertFalse($state['canFollow']);

            $I->sendDelete('space/' . self::SPACE_PUBLIC . '/follow');
            $I->seeResponseCodeIs(200);
            Assert::assertFalse($this->state($I)['isFollowing']);

            $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
            $I->seeResponseCodeIs(403);
            Assert::assertNull($this->follow(4));
        } finally {
            $module->disableFollow = false;
        }
    }

    public function testUnknownAndInvisibleSpaces(ApiTester $I)
    {
        $I->wantTo('get nothing for a space that does not exist or is invisible to me');
        $I->amLoggedInAs(4);
        $this->withCsrf($I);

        $I->sendGet('space/99999/follow');
        $I->seeResponseCodeIs(404);

        $I->sendGet('space/' . self::SPACE_PRIVATE . '/follow');
        $I->seeResponseCodeIs(403);

        $I->sendPut('space/' . self::SPACE_PRIVATE . '/follow');
        $I->seeResponseCodeIs(403);
        Assert::assertSame(0, (int)Follow::find()->where(['user_id' => 4, 'object_model' => Space::class])->count());
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendGet('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(401);

        $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(401);
    }

    public function testAStateChangingRequestNeedsACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused without a CSRF token');
        $I->amLoggedInAs(4);

        $I->sendPut('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->follow(4));
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->wantTo('find no POST route on the follow endpoint - it is set with PUT');
        $I->amLoggedInAs(4);
        $this->withCsrf($I);

        $I->sendPost('space/' . self::SPACE_PUBLIC . '/follow');
        $I->seeResponseCodeIs(404);
        Assert::assertNull($this->follow(4));
    }

    public function testTheWebFollowActionsAreGone(ApiTester $I)
    {
        $I->wantTo('find the old web follow actions removed - there is one way per transition');
        $guid = Space::findOne(['id' => self::SPACE_PUBLIC])->guid;
        $I->amLoggedInAs(4);
        $this->withCsrf($I);

        // The same route shape reaches the controller's remaining actions ...
        $I->sendGet('http://localhost:8080/space/space/follower-list?cguid=' . $guid);
        $I->seeResponseCodeIs(200);

        // ... but no longer a follow or unfollow.
        $I->sendPost('http://localhost:8080/space/space/follow?cguid=' . $guid);
        $I->seeResponseCodeIs(404);
        Assert::assertNull($this->follow(4));

        $I->sendPost('http://localhost:8080/space/space/unfollow?cguid=' . $guid);
        $I->seeResponseCodeIs(404);
    }
}
