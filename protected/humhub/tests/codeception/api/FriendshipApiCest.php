<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\serializers\FriendshipSerializer;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The friendship API (`humhub\modules\friendship\controllers\api\FriendshipController`) — the
 * endpoints the `FriendshipButton` island talks to.
 *
 * The friendship system is off by default, so every test that expects the endpoints to exist
 * enables it first ({@see self::enableFriendship()}); the settings fixture resets that between
 * tests. The friendship fixture is empty, so a test that needs an existing relation seeds the
 * rows it needs — one row means a pending request, two mean friendship.
 *
 * **One identity per test**, as in {@see CommentApiCest}.
 */
class FriendshipApiCest
{
    private const ADMIN = 1;
    private const USER1 = 2;
    private const USER2 = 3;

    private function enableFriendship(): void
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
    }

    /**
     * Arms the CSRF token pair a session-authenticated write needs, exactly like
     * `humhub.client` does in the browser.
     */
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    /**
     * `$from` asked `$to` — the row `Friendship::add()` would have written. Seeded directly
     * because the test's own identity is spent on the requests (see the class docblock), and
     * because the model's after-save side effects (notifications) need no coverage here.
     */
    private function seedRequest(int $from, int $to): void
    {
        Yii::$app->db->createCommand()->insert('user_friendship', [
            'user_id' => $from,
            'friend_user_id' => $to,
            'created_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    private function seedFriendship(int $userId, int $otherId): void
    {
        $this->seedRequest($userId, $otherId);
        $this->seedRequest($otherId, $userId);
    }

    private function state(int $userId, int $otherId): int
    {
        return Friendship::getStateForUser(
            User::findOne(['id' => $userId]),
            User::findOne(['id' => $otherId]),
        );
    }

    public function testReadsTheStateOfAStranger(ApiTester $I)
    {
        $I->wantTo('read my friendship state with a user I have no relation to');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);

        $I->sendGet('user/' . self::USER2 . '/friendship');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['state' => 'none', 'isFollowing' => false]);
    }

    public function testSendsAndWithdrawsARequest(ApiTester $I)
    {
        $I->wantTo('send a friendship request and withdraw it again');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendPost('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'requestSent']);
        Assert::assertSame(Friendship::STATE_REQUEST_SENT, $this->state(self::USER1, self::USER2));

        $I->sendDelete('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
        Assert::assertNull(Friendship::findOne(['user_id' => self::USER1, 'friend_user_id' => self::USER2]));
    }

    public function testAcceptsAReceivedRequest(ApiTester $I)
    {
        $I->wantTo('accept a friendship request with the same POST that sends one');
        $this->enableFriendship();
        $this->seedRequest(self::USER2, self::USER1);

        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendGet('user/' . self::USER2 . '/friendship');
        $I->seeResponseContainsJson(['state' => 'requestReceived']);

        $I->sendPost('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'friends']);
        Assert::assertSame(Friendship::STATE_FRIENDS, $this->state(self::USER1, self::USER2));
        // Accepting follows the other user, which the island reflects on the follow button.
        $I->seeResponseContainsJson(['isFollowing' => true]);
    }

    public function testDeniesAReceivedRequest(ApiTester $I)
    {
        $I->wantTo('deny a friendship request');
        $this->enableFriendship();
        $this->seedRequest(self::USER2, self::USER1);

        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendDelete('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
        Assert::assertSame(Friendship::STATE_NONE, $this->state(self::USER1, self::USER2));
    }

    public function testEndsAFriendship(ApiTester $I)
    {
        $I->wantTo('end an existing friendship');
        $this->enableFriendship();
        $this->seedFriendship(self::USER1, self::USER2);

        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendGet('user/' . self::USER2 . '/friendship');
        $I->seeResponseContainsJson(['state' => 'friends']);

        $I->sendDelete('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
        Assert::assertSame(Friendship::STATE_NONE, $this->state(self::USER1, self::USER2));
    }

    public function testCannotAffirmWhatIsAlreadyAffirmed(ApiTester $I)
    {
        $I->wantTo('be refused when affirming an existing friendship');
        $this->enableFriendship();
        $this->seedFriendship(self::USER1, self::USER2);

        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendPost('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(403);
        Assert::assertSame(Friendship::STATE_FRIENDS, $this->state(self::USER1, self::USER2));
    }

    public function testRemovingNothingIsASuccess(ApiTester $I)
    {
        $I->wantTo('see a DELETE without a relation answer the state instead of an error');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);
        $this->withCsrf($I);

        $I->sendDelete('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
    }

    public function testTheCallerHasNoFriendshipWithThemselves(ApiTester $I)
    {
        $I->wantTo('be refused on my own user');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);

        $I->sendGet('user/' . self::USER1 . '/friendship');
        $I->seeResponseCodeIs(403);
    }

    public function testUnknownUser(ApiTester $I)
    {
        $I->wantTo('get nothing for a user that does not exist');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);

        $I->sendGet('user/99999/friendship');
        $I->seeResponseCodeIs(404);
    }

    public function testTheEndpointDoesNotExistWhileFriendshipIsDisabled(ApiTester $I)
    {
        $I->wantTo('get a 404 while the friendship system is switched off');
        $I->amLoggedInAs(self::USER1);

        $I->sendGet('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(404);
    }

    public function testAStateChangingRequestNeedsACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused without a CSRF token');
        $this->enableFriendship();
        $I->amLoggedInAs(self::USER1);

        $I->sendPost('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(403);
        Assert::assertSame(Friendship::STATE_NONE, $this->state(self::USER1, self::USER2));
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');
        $this->enableFriendship();

        $I->sendGet('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(401);

        $I->sendPost('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(401);
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->wantTo('find no PUT route on the friendship endpoint');
        $this->enableFriendship();
        $I->amLoggedInAs(self::ADMIN);

        $I->sendPut('user/' . self::USER2 . '/friendship');
        $I->seeResponseCodeIs(404);
    }

    public function testTheStateNamesAreTheSerializerConstants(ApiTester $I)
    {
        $I->wantTo('pin the wire names of the states');
        Assert::assertSame('none', FriendshipSerializer::STATE_NONE);
        Assert::assertSame('requestSent', FriendshipSerializer::STATE_REQUEST_SENT);
        Assert::assertSame('requestReceived', FriendshipSerializer::STATE_REQUEST_RECEIVED);
        Assert::assertSame('friends', FriendshipSerializer::STATE_FRIENDS);
    }
}
