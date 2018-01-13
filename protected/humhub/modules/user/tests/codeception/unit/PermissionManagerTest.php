<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\user\tests\codeception\unit\PermissionManagerMock;
use tests\codeception\_support\HumHubDbTestCase;

class PermissionManagerTest extends HumHubDbTestCase
{

    /**
     *  Tests user approval for 1 user without group assignment and one user with group assignment.
     */
    public function testPermissionUser1()
    {
        $this->becomeUser('User1');
        $permissionManager = new PermissionManagerMock();

        $tests = [
            [true, new ManageUsers],
            [true, ManageUsers::class],
            [false, new ManageSettings],
            [false, ManageSettings::class],
            [true, [new ManageSettings, new ManageUsers]],
            [false, [new ManageSettings, new ManageUsers], ['all' => true]],
            [true, [new ManageUsers, new ManageGroups], ['all' => true]],
            [false, [ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class]],
            [false, [ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class], ['all' => true]],
            [true, [ManageSettings::class, ManageUsers::class]],
            [false, [ManageSettings::class, ManageUsers::class], ['all' => true]],
            [true, [ManageUsers::class, ManageGroups::class], ['all' => true]],
        ];

        foreach ($tests as $index => $test) {
            if (isset($test[2])) {
                $this->assertEquals($test[0], $permissionManager->can($test[1], $test[2]));
            } else {
                $this->assertEquals($test[0], $permissionManager->can($test[1]));
            }
        }
    }
}
