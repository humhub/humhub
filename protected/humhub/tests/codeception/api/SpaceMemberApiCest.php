<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\space\models\Membership;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The members of a space as rows of its member table
 * (`humhub\modules\space\controllers\api\MemberController`, `GET /api/v2/space/<id>/member`).
 *
 * Fixture space 3: owner and administrator user 1, moderator user 3, member user 2.
 */
class SpaceMemberApiCest
{
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    private function ids(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].id');
    }

    public function testListsTheMembersByRole(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('space/3/member');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 3]);
        Assert::assertSame([1, 3, 2], $this->ids($I));
        // The owner: no role to change, not removable
        Assert::assertSame(['view'], $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'));

        $I->sendGet('space/3/member', ['role' => 'moderator']);
        Assert::assertSame([3], $this->ids($I));
    }

    public function testChangesTheRoleAndRemovesAMember(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('space/3/member/2/role/moderator');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['moderator'], $I->grabDataFromResponseByJsonPath('$.row.cells.role[*].value'));
        Assert::assertSame('moderator', Membership::findOne(['space_id' => 3, 'user_id' => 2])->group_id);

        $I->sendPost('space/3/member/1/role/member');
        $I->seeResponseCodeIs(403);

        $I->sendPost('space/3/member/2/remove');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['removed' => true]);
        Assert::assertNull(Membership::findOne(['space_id' => 3, 'user_id' => 2]));
    }

    public function testRequiresTheSpaceAdministration(ApiTester $I)
    {
        // A moderator
        $I->amLoggedInAs(3);
        $I->sendGet('space/3/member');
        $I->seeResponseCodeIs(403);
    }

    public function testIsForTheSpaceAdministrators(ApiTester $I)
    {
        // An administrator of space 4
        $I->amLoggedInAs(2);
        $I->sendGet('space/4/member');
        $I->seeResponseCodeIs(200);
    }
}
