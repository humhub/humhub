<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use PHPUnit\Framework\Assert;

/**
 * The rows of the administration's group table (`humhub\modules\admin\controllers\api\GroupController`,
 * `GET /api/v2/admin/group`).
 *
 * Fixture groups: 1 Administrator (admin group; users 1, 8), 2 Users (default; user 2, its
 * manager), 3 Moderators (users 3, its manager, and 6), 4 Editors (subgroup of 3, no members).
 */
class AdminGroupApiCest
{
    private function ids(ApiTester $I): array
    {
        return $I->grabDataFromResponseByJsonPath('$.results[*].id');
    }

    public function testListsTheGroups(ApiTester $I)
    {
        $I->wantTo('list the groups, the normal ones before the subgroups');
        $I->amLoggedInAs(1);

        $I->sendGet('admin/group');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 4, 'page' => 1, 'pages' => 1]);
        Assert::assertSame([1, 3, 2, 4], $this->ids($I));
        $I->seeResponseContainsJson(['results' => [[
            'id' => 3,
            'cells' => [
                'name' => ['text' => 'Moderators', 'subtitle' => 'Example Moderator group'],
                'members' => ['text' => '2'],
                'managers' => ['text' => '1'],
                'type' => [['text' => 'Normal', 'value' => 'normal']],
            ],
        ]]]);
        // The default group can't be deleted, the moderators can.
        Assert::assertSame(['edit', 'members', 'permissions'], $I->grabDataFromResponseByJsonPath('$.results[2].actions[*].id'));
        Assert::assertSame(['edit', 'members', 'permissions', 'delete'], $I->grabDataFromResponseByJsonPath('$.results[1].actions[*].id'));
    }

    public function testSearchesFiltersAndSorts(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        // The editors' description names the moderators
        $I->sendGet('admin/group', ['q' => 'Moder']);
        Assert::assertSame([3, 4], $this->ids($I));

        $I->sendGet('admin/group', ['q' => 'Administrator Group']);
        Assert::assertSame([1], $this->ids($I));

        $I->sendGet('admin/group', ['type' => 'subgroup']);
        Assert::assertSame([4], $this->ids($I));

        // Equal member counts in the same direction by id
        $I->sendGet('admin/group', ['sort' => '-members']);
        Assert::assertSame([3, 1, 2, 4], $this->ids($I));

        $I->sendGet('admin/group', ['sort' => 'name']);
        Assert::assertSame([1, 4, 3, 2], $this->ids($I));

        $I->sendGet('admin/group', ['type' => 'other']);
        $I->seeResponseCodeIs(422);
    }

    public function testAGroupManagerSeesTheGroupsTheyManage(ApiTester $I)
    {
        $I->amLoggedInAs(3);

        $I->sendGet('admin/group');

        $I->seeResponseCodeIs(200);
        // The moderators and their subgroup
        Assert::assertSame([3, 4], $this->ids($I));
        // Deleting a group takes the group administration
        Assert::assertSame(['edit', 'members'], $I->grabDataFromResponseByJsonPath('$.results[0].actions[*].id'));
    }

    public function testRequiresTheGroupAdministration(ApiTester $I)
    {
        $I->amLoggedInAs(4);
        $I->sendGet('admin/group');
        $I->seeResponseCodeIs(403);
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->sendGet('admin/group');
        $I->seeResponseCodeIs(401);
    }
}
