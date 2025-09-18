<?php

use admin\FunctionalTester;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use yii\helpers\Url;

class GroupManagerCest
{
    public function testCanApproveUsersOnlyFromManagerGroup(FunctionalTester $I)
    {
        $I->wantTo('ensure that the group manager can approve users only from the manager group');

        $I->amUser2();

        $I->amOnRoute('/admin/user');
        $I->expectTo('redirect to pending user approvals page');
        $I->see('Pending user approvals');
        $I->see('Pending approvals', '.tab-menu');
        $I->dontSee('Overview', '.tab-menu');
        $I->dontSee('Settings', '.tab-menu');
        $I->dontSee('Profiles', '.tab-menu');
        $I->dontSee('People', '.tab-menu');
        $I->dontSee('Add new user');

        $unapprovedGroupUser = User::findOne(['username' => 'UnapprovedUser']);
        $I->see($unapprovedGroupUser->displayName);
        $I->see($unapprovedGroupUser->email);
        // Group manager has only permissions to approve/send message/decline
        $I->amOnRoute('/admin/approval/send-message', ['id' => $unapprovedGroupUser->id]);
        $I->see('Send a message to ' . $unapprovedGroupUser->displayName);
        $I->amOnRoute('/admin/approval/approve', ['id' => $unapprovedGroupUser->id]);
        $I->see('Accept user: ' . $unapprovedGroupUser->displayName);
        $I->amOnRoute('/admin/approval/decline', ['id' => $unapprovedGroupUser->id]);
        $I->see('Decline & delete user: ' . $unapprovedGroupUser->displayName);
        // But cannot edit the user
        $I->amOnRoute('/admin/user/edit', ['id' => $unapprovedGroupUser->id]);
        $I->seeResponseCodeIs(403);
        $I->see('You are not permitted to access this section.');

        $unapprovedNoGroupUser = User::findOne(['username' => 'UnapprovedNoGroup']);
        $I->dontSee($unapprovedNoGroupUser->displayName);
        $I->dontSee($unapprovedNoGroupUser->email);

        $I->amOnRoute('/admin/approval/send-message', ['id' => $unapprovedNoGroupUser->id]);
        $I->dontSee('Send a message to ' . $unapprovedNoGroupUser->displayName);
        $I->seeResponseCodeIs(404);
        $I->see('User not found!');
    }

    public function testCanManageGroups(FunctionalTester $I)
    {
        $I->wantTo('ensure that the group manager can manage only groups(and subgroups) where he is a manager');

        $I->amUser2();
        $group = Group::findOne(['name' => 'Moderators']);

        $I->amOnRoute('/admin/group');
        $I->see('Manage groups');
        $I->see('Moderators', '.table');
        $I->see('Editors', '.table'); // Subgroup can be managed too
        $I->dontSee('Administrator', '.table');
        $I->dontSee('Users', '.table');

        $I->amOnRoute('/admin/group/edit', ['id' => $group->id]);
        $I->see('Manage group: ' . $group->name);
        $I->see('Settings', '.tab-sub-menu');
        $I->dontSee('Permissions', '.tab-sub-menu');
        $I->see('Members', '.tab-sub-menu');

        $I->see('Name', 'form');
        $I->seeElement('[name="EditGroupForm[name]"][readonly]');
        $I->see('Description', 'form');
        $I->seeElement('[name="EditGroupForm[description]"][readonly]');
        $I->see('Default Space(s)', 'form');
        $I->seeElement('[name="EditGroupForm[defaultSpaceGuid][]"]');
        $I->see('Update Space memberships also for existing members.', 'form');
        $I->seeElement('[name="EditGroupForm[updateSpaceMemberships]"]');
        $I->see('Group Manager(s)', 'form');
        $I->seeElement('[name="EditGroupForm[managerGuids][]"][data-disabled]');
        $I->dontSee('Group Type', 'form');
        $I->dontSee('Enable Notifications', 'form');
        $I->dontSee('Show At Registration', 'form');
        $I->dontSee('Visible', 'form');
        $I->dontSee('Sort order', 'form');
        $I->dontSee('Default Group', 'form');

        $I->amOnRoute('/admin/group/manage-group-users', ['id' => $group->id]);
        $I->see('Manage group: ' . $group->name);
        $I->sendAjaxPostRequest(Url::toRoute('/admin/group/edit-manager-role'), [
            'id' => $group->id,
            'userId' => User::findOne(['username' => 'UnapprovedUser'])->id,
            'value' => 1,
        ]);
        $I->seeResponseCodeIs(403);
    }
}
