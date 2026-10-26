<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The open invitations and the membership requests of a space as rows of their tables
 * (`humhub\modules\space\controllers\api\InvitationController`, `ApplicantController`:
 * `GET /api/v2/space/<id>/invitation`, `/applicant`).
 *
 * Space 3 (owner and administrator user 1); `_before()` invites user 4 and lets user 8 apply,
 * `_after()` removes both.
 */
class SpaceMembershipRequestApiCest
{
    public function _before(): void
    {
        (new Membership(['space_id' => 3, 'user_id' => 4, 'status' => Membership::STATUS_INVITED, 'group_id' => Space::USERGROUP_MEMBER, 'originator_user_id' => 1]))->save(false);
        (new Membership(['space_id' => 3, 'user_id' => 8, 'status' => Membership::STATUS_APPLICANT, 'group_id' => Space::USERGROUP_MEMBER, 'request_message' => 'Let me in']))->save(false);
    }

    public function _after(): void
    {
        Membership::deleteAll(['space_id' => 3, 'user_id' => [4, 8]]);
    }

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    public function testListsTheInvitationsAndRequests(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('space/3/invitation');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([4], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('space/3/applicant');
        Assert::assertSame([8], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
        Assert::assertSame(['Let me in'], $I->grabDataFromResponseByJsonPath('$.results[0].cells.message.text'));

        // No role filter outside the members
        $I->sendGet('space/3/applicant', ['role' => 'admin']);
        $I->seeResponseCodeIs(422);
    }

    public function testCancelsAnInvitationAndAcceptsARequest(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('space/3/invitation/4/cancel');
        $I->seeResponseContainsJson(['removed' => true]);
        Assert::assertNull(Membership::findOne(['space_id' => 3, 'user_id' => 4]));

        $I->sendPost('space/3/applicant/bulk/accept', ['ids' => [8]]);
        $I->seeResponseContainsJson(['done' => 1]);
        Assert::assertSame(Membership::STATUS_MEMBER, (int)Membership::findOne(['space_id' => 3, 'user_id' => 8])->status);
    }

    public function testRequiresTheSpaceAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(3);
        $I->sendGet('space/3/invitation');
        $I->seeResponseCodeIs(403);
    }
}
