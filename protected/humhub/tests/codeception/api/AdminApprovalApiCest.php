<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The registrations awaiting approval as rows of their table
 * (`humhub\modules\admin\controllers\api\ApprovalController`, `GET /api/v2/admin/approval`).
 *
 * Fixture users awaiting approval: 6 (group 3, Moderators, managed by user 3) and 7 (no group).
 */
class AdminApprovalApiCest
{
    public function _after(): void
    {
        // Rows an API Cest writes outlive it
        User::updateAll(['status' => User::STATUS_NEED_APPROVAL], ['id' => [6, 7]]);
    }

    private function ids(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].id');
    }

    public function testListsTheRegistrationsTheViewerAdministers(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $I->sendGet('admin/approval');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([6, 7], $this->ids($I));
        Assert::assertSame(['approve', 'message', 'edit', 'decline'], $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'));
    }

    public function testAGroupManagerSeesTheRegistrationsOfTheirGroups(ApiTester $I)
    {
        $I->amLoggedInAs(3);
        $I->sendGet('admin/approval');
        $I->seeResponseCodeIs(200);
        Assert::assertSame([6], $this->ids($I));
    }

    public function testRequiresTheUserAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(4);
        $I->sendGet('admin/approval');
        $I->seeResponseCodeIs(403);
    }

    public function testApprovesInBulk(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));

        $I->sendPost('admin/approval/bulk/approve', ['ids' => [6]]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['done' => 1, 'skipped' => 0]);
        Assert::assertSame(User::STATUS_ENABLED, (int)User::findOne(6)->status);
    }
}
