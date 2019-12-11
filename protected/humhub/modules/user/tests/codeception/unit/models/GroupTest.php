<?php

namespace tests\codeception\unit\models;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\ActiveQuery;

class GroupTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        $this->assertEquals('group', Group::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new Group();
        $this->assertTrue(is_array($model->rules()));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new Group();
        $this->assertTrue(is_array($model->attributeLabels()));
    }

    public function testSaveGroup()
    {
        $model = new Group();
        $model->load([
            'name' => 'Test',
            'description' => 'Test Group',
            'space_id' => Space::findOne(['name' => 'Space 1'])->id
        ], '');

        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());
    }

    public function testReturnDefaultSpace()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        $this->assertEquals($group->space_id, $group->getDefaultSpace()->id);
    }

    public function testReturnAdminGroup()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        $this->assertEquals($group, Group::getAdminGroup());
    }

    public function testReturnAdminGroupId()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        $this->assertEquals($group->id, Group::getAdminGroupId());
    }

    public function testCheckIfGroupHasManager()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        $this->assertFalse($group->hasManager());

        $group = Group::findOne(['name' => 'Moderators']);
        $this->assertTrue($group->hasManager());
    }

    public function testCheckIfUserIsGroupManager()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        $this->assertFalse($group->isManager($user));
        $this->assertTrue($group->isManager($user2));
    }

    public function testCheckIfGroupHasUsers()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $this->assertTrue($group->hasUsers());
    }

    public function testCheckIfUserIsGroupMember()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'Admin']);
        $user2 = User::findOne(['username' => 'User2']);
        $this->assertFalse($group->isMember($user));
        $this->assertTrue($group->isMember($user2));
    }

    public function testReturnRegistrationGroups()
    {
        $groups = Group::getRegistrationGroups();
        $this->assertTrue(is_array($groups));
        $this->assertEquals($groups, Group::find()->where(['is_admin_group' => 0, 'show_at_registration' => 1])->orderBy('name ASC')->all());

        $groupUsers = Group::findOne(['name' => 'Users']);
        Yii::$app->getModule('user')->settings->set('auth.defaultUserGroup', $groupUsers->id);
        $groups = Group::getRegistrationGroups();
        $this->assertTrue(is_array($groups));
        $this->assertEquals($groups, [$groupUsers]);
    }

    public function testExcludeAdminGroupFromRegistration()
    {
        $adminGroup = Group::findOne(['is_admin_group' => 1]);
        $adminGroup->show_at_registration = 1;
        $this->assertFalse($adminGroup->save());

        // Force show at registration for admin group
        $adminGroup->updateAttributes(['show_at_registration' => 1]);
        $adminGroup = Group::findOne(['is_admin_group' => 1]);
        $this->assertEquals(1, $adminGroup->show_at_registration);

        $groups = Group::getRegistrationGroups();
        $this->assertEquals($groups, Group::find()->where(['is_admin_group' => 0, 'show_at_registration' => 1])->orderBy('name ASC')->all());
    }

    public function testReturnDirectoryGroups()
    {
        $groups = Group::getDirectoryGroups();
        $this->assertTrue(is_array($groups));
        $this->assertEquals($groups, Group::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all());
    }

    public function testAddUserToGroup()
    {
        Yii::$app->user->setIdentity(User::findOne(['username' => 'Admin']));
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        $group->addUser($user2);
        $group->addUser($user);

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject(sprintf('Notify from %s. You were added to the group.', Yii::$app->name));
    }

    public function testRemoveUserFromGroup()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        $this->assertFalse($group->removeUser($user));
        $this->assertTrue((boolean) $group->removeUser($user2));
    }

    public function testReturnSpaceRelationship()
    {
        $model = new Group();
        $this->assertTrue($model->getSpace() instanceof ActiveQuery);
    }

    public function testNotifyAdminsForUserApproval()
    {
        $user = User::findOne(['username' => 'UnapprovedUser']);
        $user2 = User::findOne(['username' => 'UnapprovedNoGroup']);
        Group::notifyAdminsForUserApproval($user);

        Yii::$app->getModule('user')->settings->set('auth.needApproval', 1);
        Group::notifyAdminsForUserApproval($user);

        $registrationGroup = Group::findOne(['name' => 'Moderators']);
        $user2->registrationGroupId = $registrationGroup->id;
        $this->assertTrue(Group::notifyAdminsForUserApproval($user2));
        $this->assertSentEmail($registrationGroup->getManager()->count());
        $this->assertEqualsLastEmailSubject('New user needs approval');

    }
}
