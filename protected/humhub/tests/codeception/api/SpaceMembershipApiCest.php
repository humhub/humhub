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
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The space membership API (`humhub\modules\space\controllers\api\MembershipController`) — the
 * endpoints the `MembershipButton` island talks to.
 *
 * The fixture spaces cover the join policies this API branches on, so no test creates a space
 * (see the constants below). Fixtures are reloaded before every test, which is what makes it
 * safe for a test to join, leave or re-configure a shared fixture space.
 *
 * **One identity per test**, as in {@see CommentApiCest}: `amLoggedInAs()` has to run before
 * the first request of a test and cannot be changed afterwards, so state another user would
 * have created (an invite) is seeded in-process.
 */
class SpaceMembershipApiCest
{
    /**
     * Public, free to join, owned by User1 (2) who is its only member — so User2 (3) can join
     * and leave it, and its owner can try to leave.
     */
    private const SPACE_FREE = 2;

    /**
     * Visible to registered users, membership needs approval, members are Admin (1) and
     * User2 (3) — so User1 (2) can apply.
     */
    private const SPACE_APPROVAL = 1;

    /**
     * Private (invisible), no self-join, members are Admin (1) and User1 (2) — so User2 (3)
     * neither sees it nor can join it, but can be invited into it.
     */
    private const SPACE_PRIVATE = 5;

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

    private function space(int $id): Space
    {
        return Space::findOne(['id' => $id]);
    }

    private function membership(int $spaceId, int $userId): ?Membership
    {
        return Membership::findOne(['space_id' => $spaceId, 'user_id' => $userId]);
    }

    /**
     * An invite for `$userId`, as another user would have created it — the identity the test
     * itself runs under is already spent on the requests (see the class docblock), so this
     * writes the row instead of calling `Space::inviteMember()`.
     */
    private function seedInvite(int $spaceId, int $userId, int $originatorId = 1): void
    {
        $now = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->insert('space_membership', [
            'space_id' => $spaceId,
            'user_id' => $userId,
            'originator_user_id' => $originatorId,
            'status' => Membership::STATUS_INVITED,
            'group_id' => Space::USERGROUP_MEMBER,
            'can_cancel_membership' => 1,
            'created_at' => $now,
            'created_by' => $originatorId,
            'updated_at' => $now,
            'updated_by' => $originatorId,
        ])->execute();
    }

    /**
     * Re-configures a fixture space, for the policies no fixture space has. Contained to the
     * running test, since the fixtures are reloaded before each of them.
     */
    private function setJoinPolicy(int $spaceId, int $policy): void
    {
        Yii::$app->db->createCommand()->update('space', ['join_policy' => $policy], ['id' => $spaceId])->execute();
    }

    public function testReadsTheStateOfANonMember(ApiTester $I)
    {
        $I->wantTo('read my membership state in a space I am not a member of');
        $I->amLoggedInAs(3);

        $I->sendGet('space/' . self::SPACE_FREE . '/membership');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'state' => 'none',
            'canJoin' => true,
            'needsApproval' => false,
            'canLeave' => false,
            'isOwner' => false,
            'isFollowing' => false,
        ]);
    }

    public function testJoinsAFreeSpaceAndLeavesAgain(ApiTester $I)
    {
        $I->wantTo('join a free space and leave it again');
        $I->amLoggedInAs(3);
        $this->withCsrf($I);

        $I->sendPost('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'member', 'canJoin' => false, 'canLeave' => true]);
        Assert::assertTrue($this->space(self::SPACE_FREE)->isMember(3));

        $I->sendDelete('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none', 'canJoin' => true, 'canLeave' => false]);
        Assert::assertNull($this->membership(self::SPACE_FREE, 3));
    }

    public function testApplyingToASpaceThatApprovesKeepsTheMessage(ApiTester $I)
    {
        $I->wantTo('apply for membership with a message');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendGet('space/' . self::SPACE_APPROVAL . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none', 'canJoin' => true, 'needsApproval' => true]);

        $I->sendPost('space/' . self::SPACE_APPROVAL . '/membership', ['message' => 'Please let me in.']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'applicant']);

        $membership = $this->membership(self::SPACE_APPROVAL, 2);
        Assert::assertSame(Membership::STATUS_APPLICANT, (int)$membership->status);
        Assert::assertSame('Please let me in.', $membership->request_message);

        // Withdrawing the application is the same DELETE that leaves a space.
        $I->sendDelete('space/' . self::SPACE_APPROVAL . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
        Assert::assertNull($this->membership(self::SPACE_APPROVAL, 2));
    }

    public function testApplyingWithoutAMessageIsAValidationFailure(ApiTester $I)
    {
        $I->wantTo('see the application message required, as in the web form');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendPost('space/' . self::SPACE_APPROVAL . '/membership', ['message' => '  ']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.message');
        Assert::assertNull($this->membership(self::SPACE_APPROVAL, 2));
    }

    public function testAcceptsAnInvite(ApiTester $I)
    {
        $I->wantTo('accept an invite through the same POST that joins');
        $this->seedInvite(self::SPACE_PRIVATE, 3);

        $I->amLoggedInAs(3);
        $this->withCsrf($I);

        // The space is private, but an invited user reaches it - otherwise the invite could
        // not be answered. Joining on their own is not possible here, the invite is.
        $I->sendGet('space/' . self::SPACE_PRIVATE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'invited', 'canJoin' => false]);

        $I->sendPost('space/' . self::SPACE_PRIVATE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'member']);
        Assert::assertTrue($this->space(self::SPACE_PRIVATE)->isMember(3));
    }

    public function testDeclinesAnInvite(ApiTester $I)
    {
        $I->wantTo('decline an invite');
        $this->seedInvite(self::SPACE_PRIVATE, 3);

        $I->amLoggedInAs(3);
        $this->withCsrf($I);

        $I->sendDelete('space/' . self::SPACE_PRIVATE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none']);
        Assert::assertNull($this->membership(self::SPACE_PRIVATE, 3));
    }

    public function testCannotJoinASpaceThatDoesNotAllowIt(ApiTester $I)
    {
        $I->wantTo('be refused when the space allows no self-join');
        $this->setJoinPolicy(self::SPACE_FREE, Space::JOIN_POLICY_NONE);

        $I->amLoggedInAs(3);
        $this->withCsrf($I);

        $I->sendGet('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['state' => 'none', 'canJoin' => false]);

        $I->sendPost('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->membership(self::SPACE_FREE, 3));
    }

    public function testTheOwnerCannotLeave(ApiTester $I)
    {
        $I->wantTo('be refused when leaving a space I own');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendGet('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'state' => 'member',
            'canJoin' => false,
            'isOwner' => true,
            'canLeave' => false,
        ]);

        $I->sendDelete('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(403);
        Assert::assertTrue($this->space(self::SPACE_FREE)->isMember(2));
    }

    public function testAStateChangingRequestNeedsACsrfToken(ApiTester $I)
    {
        $I->wantTo('be refused without a CSRF token');
        $I->amLoggedInAs(3);

        $I->sendPost('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(403);
        Assert::assertNull($this->membership(self::SPACE_FREE, 3));
    }

    public function testUnknownAndInvisibleSpaces(ApiTester $I)
    {
        $I->wantTo('get nothing for a space that does not exist or is invisible to me');
        $I->amLoggedInAs(3);

        $I->sendGet('space/99999/membership');
        $I->seeResponseCodeIs(404);

        $I->sendGet('space/' . self::SPACE_PRIVATE . '/membership');
        $I->seeResponseCodeIs(403);
    }

    public function testGuestsAreRejected(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendGet('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(401);

        $I->sendPost('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(401);
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->wantTo('find no PUT route on the membership endpoint');
        $I->amLoggedInAs(1);

        $I->sendPut('space/' . self::SPACE_FREE . '/membership');
        $I->seeResponseCodeIs(404);
    }
}
