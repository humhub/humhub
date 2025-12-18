<?php

namespace tests\codeception\unit;

use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ApprovalTest extends HumHubDbTestCase
{
    /**
     * Tests user approval for 1 user without group assignment and one user with group assignment.
     */
    public function testAdminApproval()
    {
        $this->becomeUser('Admin');

        $this->assertApprovalSearch(2);
    }

    /**
     * Tests user approval group manager.
     */
    public function testManagerApproval()
    {
        $this->becomeUser('User2');

        $this->assertApprovalSearch(1);
    }

    /**
     * Tests user approval for non group manager.
     */
    public function testNonManagerApproval()
    {
        $this->becomeUser('User1');

        $this->assertApprovalSearch(0);
    }

    public function testApprovalWithGroupManagerInheritance()
    {
        $groupA = $this->createUserGroup('Parent Group A');
        $subGroupA1 = $this->createUserGroup('Sub Group A-1', $groupA);
        $subGroupA2 = $this->createUserGroup('Sub Group A-2', $groupA);

        $this->createUser('user_parent_manager', $groupA, true);
        $this->createUser('user_parent_user', $groupA, false, User::STATUS_NEED_APPROVAL);
        $this->createUser('user_sub_a1', $subGroupA1, false, User::STATUS_NEED_APPROVAL);
        $this->createUser('user_sub_a2', $subGroupA2, false, User::STATUS_NEED_APPROVAL);

        // User can manage users from subgroup when Group Manager Inheritance is enabled
        $this->becomeUser('user_parent_manager');
        $this->assertApprovalSearch(3);

        // Disable the Group Manager Inheritance to be sure only users from the parent group are manageable
        Yii::$app->getModule('admin')->groupManagerInheritance = false;
        $this->assertApprovalSearch(1);
    }

    private function assertApprovalSearch(int $numUsers): void
    {
        $approvalSearch = new UserApprovalSearch();
        $this->assertCount($numUsers, $approvalSearch->search()->getModels());
    }

    private function createUserGroup(string $name, ?Group $parentGroup = null): Group
    {
        $group = new Group([
            'name' => $name,
            'parent_group_id' => $parentGroup?->id,
        ]);
        $this->assertTrue($group->save());

        return $group;
    }

    private function createUser(string $username, ?Group $group = null, bool $isGroupManager = false, int $status = User::STATUS_ENABLED): User
    {
        $user = new User([
            'username' => $username,
            'email' => $username . '@example.com',
            'status' => $status,
        ]);
        $this->assertTrue($user->save());
        if ($group !== null) {
            $this->assertTrue($group->addUser($user, $isGroupManager));
        }

        return $user;
    }
}
