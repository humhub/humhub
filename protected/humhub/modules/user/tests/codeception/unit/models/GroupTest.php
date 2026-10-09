<?php

namespace tests\codeception\unit\models;

use humhub\models\RecordMap;
use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

class GroupTest extends HumHubDbTestCase
{
    public function testReturnTableName()
    {
        static::assertEquals('group', Group::tableName());
    }

    public function testReturnArrayOfRules()
    {
        $model = new Group();
        static::assertTrue(is_array($model->rules()));
    }

    public function testReturnArrayOfAttributeLabels()
    {
        $model = new Group();
        static::assertTrue(is_array($model->attributeLabels()));
    }

    public function testSaveGroup()
    {
        $model = new Group();
        $model->load([
            'name' => 'Test',
            'description' => 'Test Group',
            'space_id' => Space::findOne(['name' => 'Space 1'])->id,
        ], '');

        static::assertTrue($model->validate());
        static::assertTrue($model->save());
    }

    public function testNonDefaultGroup()
    {
        $group = Group::findOne(['id' => 1]);
        static::assertEmpty($group->getDefaultSpaces());
    }

    public function testMultipleDefaultGroups()
    {
        $group = Group::findOne(['id' => 3]);
        $defaultSpaces = $group->getDefaultSpaces();
        static::assertCount(2, $defaultSpaces);
        static::assertEquals(1, $defaultSpaces[0]->id);
        static::assertEquals(2, $defaultSpaces[1]->id);
    }

    public function testSingleDefaultSpaces()
    {
        $group = Group::findOne(['id' => 2]);
        $defaultSpaces = $group->getDefaultSpaces();
        static::assertCount(1, $defaultSpaces);
        static::assertEquals(1, $defaultSpaces[0]->id);
    }

    public function testReturnAdminGroup()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        static::assertEquals($group, Group::getAdminGroup());
    }

    public function testReturnAdminGroupId()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        // check for the uncached value
        static::assertSame($group->id, Group::getAdminGroupId());
        // now check again for the cached value
        static::assertSame($group->id, Group::getAdminGroupId());
    }

    public function testCheckIfGroupHasManager()
    {
        $group = Group::findOne(['name' => 'Administrator']);
        static::assertFalse($group->hasManager());

        $group = Group::findOne(['name' => 'Moderators']);
        static::assertTrue($group->hasManager());
    }

    public function testCheckIfUserIsGroupManager()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        static::assertFalse($group->isManager($user));
        static::assertTrue($group->isManager($user2));
    }

    public function testCheckIfGroupHasUsers()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        static::assertTrue($group->hasUsers());
    }

    public function testCheckIfUserIsGroupMember()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'Admin']);
        $user2 = User::findOne(['username' => 'User2']);
        static::assertFalse($group->isMember($user));
        static::assertTrue($group->isMember($user2));
    }

    public function testReturnRegistrationGroups()
    {
        $groups = Group::getRegistrationGroups();
        static::assertTrue(is_array($groups));
        static::assertEquals($groups, Group::find()->where(['is_admin_group' => 0, 'show_at_registration' => 1])->orderBy('name ASC')->all());

        $groupUsers = Group::findOne(['name' => 'Users']);
        Yii::$app->getModule('user')->setDefaultGroup($groupUsers->id);
        $groups = Group::getRegistrationGroups();
        static::assertTrue(is_array($groups));
        static::assertEquals($groups, [$groupUsers]);
    }

    public function testExcludeAdminGroupFromRegistration()
    {
        $adminGroup = Group::findOne(['is_admin_group' => 1]);
        $adminGroup->show_at_registration = 1;
        static::assertTrue($adminGroup->save());
        // Must be ignored on save by scenario rule
        $this->assertEquals($adminGroup->show_at_registration, 0);

        // Force show at registration for admin group
        $adminGroup->updateAttributes(['show_at_registration' => 1]);
        $adminGroup = Group::findOne(['is_admin_group' => 1]);
        static::assertEquals(1, $adminGroup->show_at_registration);

        $groups = Group::getRegistrationGroups();
        static::assertEquals($groups, Group::find()->where(['is_admin_group' => 0, 'show_at_registration' => 1])->orderBy('name ASC')->all());
    }

    public function testReturnDirectoryGroups()
    {
        $groups = Group::getDirectoryGroups();
        static::assertTrue(is_array($groups));
        static::assertEquals($groups, Group::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all());
    }

    public function testAddUserToGroup()
    {
        Yii::$app->user->setIdentity(User::findOne(['username' => 'Admin']));
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        $group->notify_users = true;
        $group->addUser($user2);
        $group->addUser($user);

        $this->assertSentEmail(1);
        $this->assertEqualsLastEmailSubject(User::findOne(['username' => 'Admin'])->displayName . ' added you to group “Moderators”');
    }

    public function testRemoveUserFromGroup()
    {
        $group = Group::findOne(['name' => 'Moderators']);
        $user = User::findOne(['username' => 'User1']);
        $user2 = User::findOne(['username' => 'User2']);
        static::assertFalse($group->removeUser($user));
        static::assertTrue((bool)$group->removeUser($user2));
    }

    public function testAddingAUserToAGroupNotifiesThem()
    {
        $this->becomeUser('Admin');
        $group = $this->createNotifyingGroup('Group <b>One</b>');
        $user = User::findOne(['username' => 'User1']);

        $this->assertTrue($group->addUser($user));

        $this->assertHasNotification(IncludeGroupNotification::class, $group, 1, $user->id);
        $record = Notification::findOne(['class' => IncludeGroupNotification::class, 'user_id' => $user->id]);
        $this->assertSame(RecordMap::getId($group), (int)$record->source_record_id);
        $this->assertSame(NotificationPriority::Normal->value, (int)$record->priority);
        $this->assertSame(NotificationCategory::ID_DIRECT, IncludeGroupNotification::category()->id);

        $notification = NotificationManager::load($record);
        $admin = User::findOne(['username' => 'Admin']);
        $this->assertSame(
            '<strong>' . $admin->displayName . '</strong> added you to group <strong>Group &lt;b&gt;One&lt;/b&gt;</strong>',
            $notification->asWeb(),
        );
        $this->assertSame($admin->displayName . ' added you to group “Group <b>One</b>”', $notification->asMailText());
        $this->assertSame($admin->displayName . ' added you to group “Group <b>One</b>”', $notification->asMailSubject());
        $this->assertSame(Url::to(['/user/people']), $notification->getUrl());
    }

    public function testRemovingAUserFromAGroupNotifiesThem()
    {
        $this->becomeUser('Admin');
        $group = $this->createNotifyingGroup('Group One');
        $user = User::findOne(['username' => 'User1']);
        $this->assertTrue($group->addUser($user));

        $this->assertTrue((bool)$group->removeUser($user));

        $this->assertHasNotification(ExcludeGroupNotification::class, $group, 1, $user->id);
        $record = Notification::findOne(['class' => ExcludeGroupNotification::class, 'user_id' => $user->id]);
        $this->assertSame(NotificationPriority::Normal->value, (int)$record->priority);
        $this->assertSame(NotificationCategory::ID_DIRECT, ExcludeGroupNotification::category()->id);

        $notification = NotificationManager::load($record);
        $this->assertSame(
            '<strong>' . User::findOne(['username' => 'Admin'])->displayName . '</strong> removed you from group <strong>Group One</strong>',
            $notification->asWeb(),
        );
    }

    public function testRemovingAUserWithoutACurrentUserSendsNothing()
    {
        $this->becomeUser('Admin');
        $group = $this->createNotifyingGroup('Group One');
        $user = User::findOne(['username' => 'User1']);
        $this->assertTrue($group->addUser($user));

        $this->logout();
        $this->assertTrue((bool)$group->removeUser($user));

        $this->assertSame(0, (int)Notification::find()->where(['class' => ExcludeGroupNotification::class])->count());
    }

    public function testAddingAUserAgainNotifiesThemAgain()
    {
        $this->becomeUser('Admin');
        $group = $this->createNotifyingGroup('Group One');
        $user = User::findOne(['username' => 'User1']);

        $this->assertTrue($group->addUser($user));
        $this->assertTrue((bool)$group->removeUser($user));
        $this->assertTrue($group->addUser($user));

        $this->assertSame(2, (int)Notification::find()->where(['class' => IncludeGroupNotification::class, 'user_id' => $user->id])->count());
    }

    public function testDeletingTheGroupDeletesItsNotifications()
    {
        $this->becomeUser('Admin');
        $group = $this->createNotifyingGroup('Group One');
        $user = User::findOne(['username' => 'User1']);
        $this->assertTrue($group->addUser($user));
        $this->assertHasNotification(IncludeGroupNotification::class, $group, 1, $user->id);

        $this->assertNotFalse($group->delete());

        $this->assertSame(0, (int)Notification::find()->where(['class' => IncludeGroupNotification::class])->count());
    }

    private function createNotifyingGroup(string $name): Group
    {
        $group = new Group(['name' => $name, 'notify_users' => 1]);
        $this->assertTrue($group->save());

        return $group;
    }

    public function testReturnSpaceRelationship()
    {
        $model = new Group();
        static::assertTrue($model->getGroupSpaces() instanceof ActiveQuery);
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
        static::assertTrue(Group::notifyAdminsForUserApproval($user2));
        $this->assertSentEmail($registrationGroup->getManager()->count());
        $this->assertEqualsLastEmailSubject('New user needs approval');

    }
}
