<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\libs\BasePermission;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupSpace;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class PermissionManagerTest extends HumHubDbTestCase
{
    public function testUserPermission()
    {
        $this->becomeUser('User1');

        // Check the current user has no the permissions by default
        $this->checkPermissions([
            [false, ManageUsers::class],
            [false, ManageGroups::class],
        ]);

        // Enable the permissions for current user
        $this->setUserPermission(ManageUsers::class);
        $this->setUserPermission(ManageGroups::class);
        $this->checkPermissions([
            [true, new ManageUsers()],
            [true, ManageUsers::class],
            [false, new ManageSettings()],
            [false, ManageSettings::class],
            [true, [new ManageSettings(), new ManageUsers()]],
            [false, [new ManageSettings(), new ManageUsers()], ['all' => true]],
            [true, [new ManageUsers(), new ManageGroups()], ['all' => true]],
            [false, [ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class]],
            [false, [ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class], ['all' => true]],
            [true, [ManageSettings::class, ManageUsers::class]],
            [false, [ManageSettings::class, ManageUsers::class], ['all' => true]],
            [true, [ManageUsers::class, ManageGroups::class], ['all' => true]],
        ]);
    }

    public function testSubgroupPermissions()
    {
        $user = $this->becomeUser('User2');

        // Check the current user has no the permissions by default
        $this->checkPermissions([
            [false, ManageGroups::class],
        ]);

        [$parentGroup, $subGroup] = $this->checkParentSubGroups();

        // Link the current user only to the subgroup
        $parentGroup->removeUser($user->id);
        $subGroup->addUser($user->id);
        $this->assertUserGroups($subGroup, $parentGroup);

        // Check the user from subgroup has same permissions as parent group has
        self::setGroupPermission($parentGroup->id, ManageGroups::class);
        $this->checkPermissions([
            [true, ManageGroups::class],
        ]);
    }

    public function testParentGroupManagerPermissions()
    {
        $user = $this->becomeUser('User2');

        [$parentGroup, $subGroup] = $this->checkParentSubGroups();
        $defaultGroup = Group::findOne(['name' => 'Users']);
        $parentGroupSpace = $this->addDefaultSpaceToGroup($parentGroup, 1);

        // Make sure the user is a manager only of the parent group
        $this->assertUserGroups($parentGroup, $subGroup);
        $this->assertTrue($parentGroup->isManager($user));
        $this->assertFalse($subGroup->isManager($user));
        $this->assertFalse($defaultGroup->isManager($user));

        Yii::$app->getModule('user')->settings->set('auth.needApproval', 1);

        // Create an unapproved user linked to the default group "Users"
        $unapprovedDefaultGroupUser = $this->createUnapprovedUser('default_user', $defaultGroup);
        $this->assertInstanceOf(User::class, $unapprovedDefaultGroupUser);

        // Create an unapproved user linked only to the subgroup
        $unapprovedSubGroupUser = $this->createUnapprovedUser('sub_group_user', $subGroup);
        $this->assertInstanceOf(User::class, $unapprovedSubGroupUser);

        // Check the current user can manage users from the subgroup:
        $searchModel = new UserApprovalSearch();
        $dataProvider = $searchModel->search();
        $unapprovedUsers = $dataProvider->query
            ->andWhere(['user.id' => [$unapprovedSubGroupUser->id, $unapprovedDefaultGroupUser->id]]);
        // to be sure only single user is selected
        $this->assertEquals(1, $unapprovedUsers->count());
        // and the user is from the subgroup and not from the default group
        $this->assertEquals($unapprovedSubGroupUser->id, $unapprovedUsers->select('user.id')->scalar());
        // and the user has received the expected approval email notification
        $this->assertEqualsLastEmailSubject('New user needs approval');
        $this->assertEqualsLastEmailTo($user->email);

        // Make sure the user from subgroup is also added to Default Spaces of the parent group:
        $this->assertTrue($parentGroupSpace->isMember($unapprovedSubGroupUser));

        // Enable the user before searching on the People page
        $unapprovedSubGroupUser->status = User::STATUS_ENABLED;
        $unapprovedSubGroupUser->save();

        // Make sure on "People" if the parent group is selected the users from the subgroup will be shown as well:
        $peopleQuery = new PeopleQuery(['defaultFilters' => ['groupId' => $parentGroup->id]]);
        $people = $peopleQuery->select('user.username')->column();
        $this->assertEquals([$user->username, $unapprovedSubGroupUser->username], $people);
    }

    private function assertUserGroups($hasGroups = [], $hasNotGroups = []): void
    {
        $userGroupIds = GroupUser::find()
            ->select('group_id')
            ->where(['user_id' => Yii::$app->user->id])
            ->column();

        if (!is_array($hasGroups)) {
            $hasGroups = [$hasGroups];
        }
        foreach ($hasGroups as $hasGroup) {
            $this->assertContains($hasGroup->id, $userGroupIds);
        }

        if (!is_array($hasNotGroups)) {
            $hasNotGroups = [$hasNotGroups];
        }
        foreach ($hasNotGroups as $hasNotGroup) {
            $this->assertNotContains($hasNotGroup->id, $userGroupIds);
        }
    }

    /**
     * @return Group[]
     */
    private function checkParentSubGroups(): array
    {
        $parentGroup = Group::findOne(['name' => 'Moderators']);
        $subGroup = Group::findOne(['name' => 'Editors']);
        $this->assertEquals($parentGroup->id, $subGroup->parent_group_id);

        return [$parentGroup, $subGroup];
    }

    private function checkPermissions($tests): void
    {
        foreach ($tests as $test) {
            $this->assertEquals($test[0], Yii::$app->user->can($test[1], $test[2] ?? []));
        }
    }

    private function setUserPermission(string $permission, $state = BasePermission::STATE_ALLOW): void
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return;
        }

        $groupId = $user->identity->getGroups()->select('id')->scalar();
        if ($groupId) {
            self::setGroupPermission($groupId, $permission, $state);
        }
    }

    private function createUnapprovedUser(string $username, Group $group): ?User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $username . '@example.com';
        $user->status = User::STATUS_NEED_APPROVAL;
        $user->registrationGroupId = $group->id;
        if (!$user->save()) {
            return null;
        }

        $group->addUser($user);

        return $user;
    }

    private function addDefaultSpaceToGroup(Group $group, int $spaceId): Space
    {
        $space = Space::findOne(['id' => $spaceId]);
        $this->assertInstanceOf(Space::class, $space);

        if ($group->getGroupSpaces()->andWhere(['space_id' => $space->id])->exists()) {
            return $space;
        }

        $groupSpace = new GroupSpace();
        $groupSpace->group_id = $group->id;
        $groupSpace->space_id = $space->id;
        $this->assertTrue($groupSpace->save());

        return $space;
    }
}
