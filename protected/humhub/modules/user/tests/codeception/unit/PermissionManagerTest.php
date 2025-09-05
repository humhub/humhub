<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\libs\BasePermission;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
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
        $this->becomeUser('User2');

        // Check the current user has no the permissions by default
        $this->checkPermissions([
            [false, ManageGroups::class],
        ]);

        // Check a subgroup exists
        $parentGroup = Group::findOne(3);
        $subGroup = Group::findOne(4);
        $this->assertEquals($parentGroup->id, $subGroup->parent_group_id);

        // Link the current user only to the subgroup
        $parentGroup->removeUser(Yii::$app->user->id);
        $subGroup->addUser(Yii::$app->user->id);
        $userGroupIds = GroupUser::find()->select('group_id')->where(['user_id' => Yii::$app->user->id])->column();
        $this->assertNotContains($parentGroup->id, $userGroupIds);
        $this->assertContains($subGroup->id, $userGroupIds);

        // Check the user from subgroup has same permissions as parent group has
        self::setGroupPermission($parentGroup->id, ManageGroups::class);
        $this->checkPermissions([
            [true, ManageGroups::class],
        ]);
    }

    private function checkPermissions($tests)
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
}
