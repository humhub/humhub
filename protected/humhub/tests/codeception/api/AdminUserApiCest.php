<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The rows of the administration's user table (`humhub\modules\admin\controllers\api\UserController`,
 * `GET /api/v2/admin/user`, `POST /api/v2/admin/user/<id>/enable|disable`) — what the
 * `DataTable` island on Administration › Users loads.
 *
 * Fixture users: active 1 (Admin Tester, administrator), 2 (Peter Tester), 3 (Sara Tester,
 * Moderators), 4 (Andreas Tester), 8 (AdminNotMember User, administrator); 5 deactivated.
 */
class AdminUserApiCest
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

    public function testListsTheActiveUsersNewestFirst(ApiTester $I)
    {
        $I->wantTo('list the active users, the newest account first');
        $I->amLoggedInAs(1);

        $I->sendGet('admin/user');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 5, 'page' => 1, 'pages' => 1]);
        Assert::assertSame([8, 4, 3, 2, 1], $this->ids($I));
        $I->seeResponseContainsJson(['results' => [[
            'id' => 3,
            'cells' => [
                'name' => ['text' => 'Sara Tester', 'subtitle' => 'user2@example.com'],
                'groups' => [['text' => 'Moderators', 'value' => '3']],
                'lastLogin' => ['text' => 'never'],
            ],
        ]]]);
        // The viewer's own row offers no "Delete".
        $own = $I->grabDataFromResponseByJsonPath('$.results[4].actions[*].id');
        Assert::assertSame(['edit', 'disable', 'profile'], $own);
        Assert::assertSame(['edit', 'disable', 'impersonate', 'profile', 'delete'], $I->grabDataFromResponseByJsonPath('$.results[3].actions[*].id'));
    }

    public function testSearchesFiltersAndSorts(ApiTester $I)
    {
        $I->wantTo('narrow and order the users like the table does');
        $I->amLoggedInAs(1);

        $I->sendGet('admin/user', ['q' => 'Sara']);
        Assert::assertSame([3], $this->ids($I));

        $I->sendGet('admin/user', ['q' => 'user1@example']);
        Assert::assertSame([2], $this->ids($I));

        $I->sendGet('admin/user', ['status' => 'deactivated']);
        Assert::assertSame([5], $this->ids($I));
        Assert::assertSame(['edit', 'enable', 'delete'], $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'));

        // Moderators: Sara and an unapproved account, which is no active user.
        $I->sendGet('admin/user', ['group' => '3']);
        Assert::assertSame([3], $this->ids($I));

        $I->sendGet('admin/user', ['sort' => 'name']);
        Assert::assertSame([1, 4, 2, 3, 8], $this->ids($I));

        $I->sendGet('admin/user', ['sort' => '-name']);
        Assert::assertSame([8, 3, 2, 4, 1], $this->ids($I));
    }

    public function testPagesTheList(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/user', ['pageSize' => 2, 'page' => 2]);

        $I->seeResponseContainsJson(['total' => 5, 'page' => 2, 'pageSize' => 2, 'pages' => 3]);
        Assert::assertSame([3, 2], $this->ids($I));
    }

    public function testRejectsUnknownParametersAndValues(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/user', ['status' => 'everyone']);
        $I->seeResponseCodeIs(422);

        $I->sendGet('admin/user', ['sort' => 'password']);
        $I->seeResponseCodeIs(422);

        $I->sendGet('admin/user', ['color' => 'red']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson(['errors' => ['color' => ['Unknown parameter.']]]);
    }

    public function testRequiresTheUserAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(2);

        $I->sendGet('admin/user');

        $I->seeResponseCodeIs(403);
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->sendGet('admin/user');

        $I->seeResponseCodeIs(401);
    }

    public function testDisablesAndEnablesAUser(ApiTester $I)
    {
        $I->wantTo('disable and enable a user and get the new row back');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('admin/user/2/disable');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['row' => ['id' => 2]]);
        Assert::assertSame(['edit', 'enable', 'delete'], $I->grabDataFromResponseByJsonPath('$.row.actions[*].id'));
        Assert::assertEquals(User::STATUS_DEACTIVATED, User::findOne(2)->status);

        $I->sendPost('admin/user/2/enable');

        $I->seeResponseCodeIs(200);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(2)->status);
    }

    public function testRefusesWhatTheRowDoesNotOffer(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('admin/user/5/disable');
        $I->seeResponseCodeIs(403);

        $I->sendPost('admin/user/999/disable');
        $I->seeResponseCodeIs(404);

        $I->sendGet('admin/user/2/disable');
        $I->seeResponseCodeIs(404);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(2)->status);
    }

    public function testChangingTheStatusWithoutACsrfTokenIsRefused(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendPost('admin/user/2/disable');

        $I->seeResponseCodeIs(403);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(2)->status);
    }

    public function testDisablesAndEnablesTheSelectedUsers(ApiTester $I)
    {
        $I->wantTo('change the status of several users at once');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        // 5 is deactivated already: its row does not offer "disable".
        $I->sendPost('admin/user/bulk/disable', ['ids' => [2, 3, 5]]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['done' => 2, 'skipped' => 1]);
        Assert::assertEquals(User::STATUS_DEACTIVATED, User::findOne(2)->status);
        Assert::assertEquals(User::STATUS_DEACTIVATED, User::findOne(3)->status);

        // Every user of the filters: the deactivated ones (2, 3, 5)
        $I->sendPost('admin/user/bulk/enable?status=deactivated', ['all' => 1]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['done' => 3, 'skipped' => 0]);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(2)->status);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(5)->status);
    }

    public function testRefusesAnInvalidBulkRequest(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('admin/user/bulk/disable', ['ids' => []]);
        $I->seeResponseCodeIs(422);

        $I->sendPost('admin/user/bulk/disable', ['ids' => ['x']]);
        $I->seeResponseCodeIs(422);

        $I->sendPost('admin/user/bulk/disable?status=everyone', ['all' => 1]);
        $I->seeResponseCodeIs(422);

        $I->sendPost('admin/user/bulk/delete', ['ids' => [2]]);
        $I->seeResponseCodeIs(404);
    }

    public function testTheBulkChangeRequiresTheUserAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendPost('admin/user/bulk/disable', ['ids' => [3]]);

        $I->seeResponseCodeIs(403);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(3)->status);
    }
}
