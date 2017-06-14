<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\user\tests\codeception\unit\PermissionManagerMock;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Follow;

class PermissionManagerTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Tests user approval for 1 user without group assignment and one user with group assignment.
     */
    public function testPermissions()
    {
        $this->becomeUser('User1');

        $permissionManager = new PermissionManagerMock();
        $this->assertTrue($permissionManager->can(new ManageUsers));
        $this->assertTrue($permissionManager->can(ManageUsers::class));
        $this->assertFalse($permissionManager->can(new ManageSettings));

        $this->assertTrue($permissionManager->can([new ManageSettings, new ManageUsers]));
        $this->assertFalse($permissionManager->can([new ManageSettings, new ManageUsers], ['all' => true]));
        $this->assertTrue($permissionManager->can([new ManageUsers, new ManageGroups], ['all' => true]));
        $this->assertFalse($permissionManager->can([ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class]));
        $this->assertFalse($permissionManager->can([ManageSettings::class, ManageSpaces::class, SeeAdminInformation::class], ['all' => true]));

        $this->assertTrue($permissionManager->can([ManageSettings::class, ManageUsers::class]));
        $this->assertFalse($permissionManager->can([ManageSettings::class, ManageUsers::class], ['all' => true]));
        $this->assertTrue($permissionManager->can([ManageUsers::class, ManageGroups::class], ['all' => true]));
    }


}
