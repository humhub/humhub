<?php

namespace tests\codeception\unit\modules\space;


use humhub\libs\BasePermission;
use humhub\modules\admin\models\forms\UserEditForm;
use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class GroupsChangesTest extends HumHubDbTestCase
{

    public function testUserManagerTryToAssignAdminGroupOnHimself()
    {
        $this->becomeUser('Admin');

        // Add User1 to Usermanager Group
        $group = new EditGroupForm(['name' => 'UserManager', 'managerGuids' => [User::findOne(['id' => 2])->guid]]);
        $this->assertTrue($group->save());

        Yii::$app->user->permissionManager->setGroupState($group->id, ManageUsers::class, BasePermission::STATE_ALLOW);

        $group2 = new EditGroupForm(['name' => 'SomeOtherGroup', 'managerGuids' => [User::findOne(['id' => 2])->guid]]);
        $this->assertTrue($group2->save());

        $this->assertCount(1, $group->manager);
        $this->assertContains('Administrator', array_values(UserEditForm::getGroupItems()));

        $this->becomeUser('User1');
        $this->assertNotContains('Administrator', array_values(UserEditForm::getGroupItems()));

        $userEditForm = UserEditForm::findOne(['id' => 2]);
        $userEditForm->initGroupSelection();
        $this->assertEquals('Groups', $userEditForm->getGroupLabel());

        $userEditForm->groupSelection = [Group::getAdminGroupId(), $group2->id];
        $this->assertTrue($userEditForm->save());

        $user1 = User::findOne(['id' => 2]);

        // Make sure the user could not add the admin group to himself
        $adminGroup = Group::getAdminGroup();
        $this->assertFalse($adminGroup->isMember($user1));
        $this->assertTrue($group2->isMember($user1));
        $this->assertCount(1, $user1->getGroups()->all());
    }

    public function testUserManagerTryToRemoveAdminGroupOnAdmin()
    {
        $this->becomeUser('Admin');

        // Add User1 to Usermanager Group
        $group = new EditGroupForm(['name' => 'UserManager', 'managerGuids' => [User::findOne(['id' => 2])->guid]]);
        $this->assertTrue($group->save());

        Yii::$app->user->permissionManager->setGroupState($group->id, ManageUsers::class, BasePermission::STATE_ALLOW);

        $group2 = new EditGroupForm(['name' => 'SomeOtherGroup', 'managerGuids' => [User::findOne(['id' => 2])->guid]]);
        $this->assertTrue($group2->save());

        $this->assertCount(1, $group->manager);
        $this->assertContains('Administrator', array_values(UserEditForm::getGroupItems()));

        $this->becomeUser('User1');
        $this->assertNotContains('Administrator', array_values(UserEditForm::getGroupItems()));

        $userEditForm = UserEditForm::findOne(['id' => 1]);
        $userEditForm->initGroupSelection();
        $this->assertEquals('Groups (Note: The Administrator group of this user can\'t be managed with your permissions)', $userEditForm->getGroupLabel());

        $userEditForm->groupSelection = [$group2->id];
        $this->assertTrue($userEditForm->save());

        $admin = User::findOne(['id' => 1]);

        // Make sure the admin is still admin member
        $adminGroup = Group::getAdminGroup();
        $this->assertTrue($adminGroup->isMember($admin));
        $this->assertTrue($group2->isMember($admin));
        $this->assertCount(2, $admin->getGroups()->all());
    }

    public function testAdminAssignsAdminGroupToOtherUser()
    {
        $this->becomeUser('Admin');

        $this->assertContains('Administrator', array_values(UserEditForm::getGroupItems()));

        $userEditForm = UserEditForm::findOne(['id' => 2]);
        $userEditForm->initGroupSelection();
        $this->assertEquals('Groups', $userEditForm->getGroupLabel());

        $userEditForm->groupSelection = [Group::getAdminGroupId()];
        $this->assertTrue($userEditForm->save());

        $user1 = User::findOne(['id' => 2]);

        // Make sure the admin could assign admin group to user1
        $adminGroup = Group::getAdminGroup();
        $this->assertTrue($adminGroup->isMember($user1));
        $this->assertCount(1, $user1->getGroups()->all());
    }

    public function testAddUserToGroupNotify()
    {
        /** @var Group $group */
        $group = Group::findOne(['id' => 1]);

        $notify = IncludeGroupNotification::instance();

        $notify
            ->about($group)
            ->from(User::findOne(['id' => 1]))
            ->send(User::findOne(['id' => 2]));

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject($notify->getMailSubject());
    }

    public function testRemoveUserToGroupNotify()
    {
        $group = Group::findOne(['id' => 1]);
        $notify = ExcludeGroupNotification::instance();

        $notify
            ->about($group)
            ->from(User::findOne(['id' => 1]))
            ->send(User::findOne(['id' => 2]));

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject($notify->getMailSubject());
    }
}
