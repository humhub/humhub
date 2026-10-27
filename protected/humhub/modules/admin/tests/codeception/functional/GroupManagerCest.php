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
        $I->see('Pending approvals', '.content-card .card-title');
        $I->see('Pending approvals', '.nav-pills');
        $I->dontSee('Overview', '.nav-pills');
        $I->dontSee('Settings', '.nav-pills');
        $I->dontSee('Profiles', '.nav-pills');
        $I->dontSee('People', '.nav-pills');
        $I->dontSee('Add new user');

        $unapprovedGroupUser = User::findOne(['username' => 'UnapprovedUser']);
        $unapprovedNoGroupUser = User::findOne(['username' => 'UnapprovedNoGroup']);
        // The rows of the table (AdminApprovalApiCest)
        $I->seeElement('.data-table');
        // Group manager has only permissions to approve/send message/decline
        // The dialogs of the approval table
        $I->sendAjaxGetRequest(Url::to(['/admin/approval/send-message', 'id' => $unapprovedGroupUser->id]));
        $I->seeInSource('Send a message to ' . $unapprovedGroupUser->displayName);
        $I->sendAjaxGetRequest(Url::to(['/admin/approval/approve', 'id' => $unapprovedGroupUser->id]));
        $I->seeInSource('Accept user: ' . $unapprovedGroupUser->displayName);
        $I->sendAjaxGetRequest(Url::to(['/admin/approval/decline', 'id' => $unapprovedGroupUser->id]));
        $I->seeInSource('Decline &amp; delete user: ' . $unapprovedGroupUser->displayName);
        // But cannot edit the user
        $I->amOnRoute('/admin/user/edit', ['id' => $unapprovedGroupUser->id]);
        $I->seeResponseCodeIs(403);
        $I->see('You are not permitted to access this section.');


        $I->sendAjaxGetRequest(Url::to(['/admin/approval/send-message', 'id' => $unapprovedNoGroupUser->id]));
        $I->dontSeeInSource('Send a message to ' . $unapprovedNoGroupUser->displayName);
        $I->seeResponseCodeIs(404);
        $I->see('User not found!');
    }

    public function testCanManageGroups(FunctionalTester $I)
    {
        $I->wantTo('ensure that the group manager can manage only groups(and subgroups) where he is a manager');

        $I->amUser2();
        $group = Group::findOne(['name' => 'Moderators']);

        $I->amOnRoute('/admin/group');
        $I->see('Groups', '.content-card .card-title');
        // The rows come from GET /api/v2/admin/group, see AdminGroupApiCest
        $I->seeElement('.data-table');

        $I->amOnRoute('/admin/group/edit', ['id' => $group->id]);
        $I->see('Manage group: ' . $group->name);
        $I->see('Settings', '.nav-pills-secondary');
        $I->dontSee('Permissions', '.nav-pills-secondary');
        $I->see('Members', '.nav-pills-secondary');

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
        $I->seeElement('.data-table');
    }
}
