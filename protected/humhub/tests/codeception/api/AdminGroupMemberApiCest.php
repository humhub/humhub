<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\user\models\GroupUser;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The members of a group as rows of its member table
 * (`humhub\modules\admin\controllers\api\GroupMemberController`, `GET /api/v2/admin/group/<id>/member`).
 *
 * Fixture groups: 1 Administrator (users 1, 8), 2 Users (user 2, its manager), 3 Moderators
 * (user 3, its manager, and the unapproved user 6, not listed); each test adds user 4 to group 3.
 */
class AdminGroupMemberApiCest
{
    public function _before(): void
    {
        (new GroupUser(['group_id' => 3, 'user_id' => 4]))->save();
    }

    public function _after(): void
    {
        // Rows an API Cest writes outlive it
        GroupUser::deleteAll(['user_id' => 4, 'group_id' => [2, 3]]);
        GroupUser::updateAll(['is_group_manager' => 0], ['user_id' => 6, 'group_id' => 3]);
    }

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

    public function testListsTheMembersManagersFirst(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendGet('admin/group/3/member');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 2]);
        Assert::assertSame([3, 4], $this->ids($I));
        Assert::assertSame(['manager'], $I->grabDataFromResponseByJsonPath('$.results[0].cells.role[*].value'));

        $I->sendGet('admin/group/3/member', ['role' => 'member']);
        Assert::assertSame([4], $this->ids($I));

        $I->sendGet('admin/group/3/member', ['role' => 'other']);
        $I->seeResponseCodeIs(422);

        $I->sendGet('admin/group/99/member');
        $I->seeResponseCodeIs(404);
    }

    public function testMakesAndUnmakesAGroupManager(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('admin/group/3/member/4/manager');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['manager'], $I->grabDataFromResponseByJsonPath('$.row.cells.role[*].value'));
        Assert::assertSame(1, (int)GroupUser::findOne(['group_id' => 3, 'user_id' => 4])->is_group_manager);

        $I->sendPost('admin/group/3/member/4/member');
        $I->seeResponseCodeIs(200);
        Assert::assertSame(0, (int)GroupUser::findOne(['group_id' => 3, 'user_id' => 4])->is_group_manager);

        // Not in the administrators' group
        $I->sendPost('admin/group/1/member/8/manager');
        $I->seeResponseCodeIs(403);
    }

    public function testRemovesOnlyMembersInAnotherGroup(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        // User 4 is in the moderators' group only
        $I->sendPost('admin/group/3/member/4/remove');
        $I->seeResponseCodeIs(403);

        (new GroupUser(['group_id' => 2, 'user_id' => 4]))->save();
        $I->sendPost('admin/group/3/member/4/remove');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['removed' => true]);
        Assert::assertNull(GroupUser::findOne(['group_id' => 3, 'user_id' => 4]));
    }

    public function testAGroupManagerSeesTheMembersButMakesNoManagers(ApiTester $I)
    {
        $I->amLoggedInAs(3);

        $I->sendGet('admin/group/3/member');
        $I->seeResponseCodeIs(200);
        Assert::assertNotContains('manager', $I->grabDataFromResponseByJsonPath('$.results[*].actions[*].id'));

        $this->withCsrf($I);
        $I->sendPost('admin/group/3/member/4/manager');
        $I->seeResponseCodeIs(403);

        $I->sendGet('admin/group/2/member');
        $I->seeResponseCodeIs(403);
    }
}
